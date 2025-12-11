<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\SystemSetting; // Import SystemSetting
use App\Models\User; // Import User
use App\Models\Izin;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Pagination\LengthAwarePaginator; // Import Paginator
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // Import Period
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // --- Attendances Unified Data ---
        $reportData = $this->getReportData($request);
        
        // Filter out "Izin" records for the Presensi Tab view
        $displayData = $reportData->filter(function($row) {
            return stripos($row->status, 'Izin') === false;
        });
        
        // Manual Pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage('attendances_page');
        $perPage = 10;
        $currentItems = $displayData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $attendances = new LengthAwarePaginator(
            $currentItems, 
            $displayData->count(), 
            $perPage, 
            $currentPage, 
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'attendances_page',
            ]
        );
        $attendances->appends($request->all());

        // --- Permissions (Izin) Query (Maintained for separate tab) ---
        $permissionsQuery = Izin::with(['user'])->orderBy('created_at', 'desc');

        if ($request->search) {
            $permissionsQuery->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->start_date) {
            $permissionsQuery->where('tanggal_mulai', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $permissionsQuery->where('tanggal_selesai', '<=', $request->end_date);
        }

        $permissions = $permissionsQuery->paginate(10, ['*'], 'permissions_page')->withQueryString();
        
        return Inertia::render('SuperAdmin/LaporanGlobal', [
            'attendances' => $attendances,
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'start_date', 'end_date']),
        ]);
    }
    
    public function exportExcel(Request $request)
    {
        $reportData = $this->getReportData($request);
        return Excel::download(new LaporanExport($reportData), 'laporan-absensi.xlsx');
    }
    
    public function exportPDF(Request $request)
    {
        $reportData = $this->getReportData($request);
        
        $now = Carbon::now();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : $now->copy();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : $now->copy();

        $pdf = DomPdf::loadView('pdf.laporan_absensi', [
            'attendances' => $reportData,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);
        return $pdf->download('laporan-absensi.pdf');
    }

    private function getReportData(Request $request)
    {
        $setting = SystemSetting::first();
        // Default cutoff 17:00:00 if not set
        $cutoffTime = $setting ? ($setting->cutoff_time ?? '17:00:00') : '17:00:00';

        // Get date range
        $now = Carbon::now();
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : $now->copy()->endOfMonth()->format('Y-m-d');
        $search = $request->search;
        
        $usersQuery = User::query();
        if ($search) {
             $usersQuery->where('name', 'like', "%$search%");
        }
        $users = $usersQuery->get();
        
        // Pre-fetch Absensi
        $absensis = Absensi::with('user')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();
            
        // Pre-fetch Approved/Disetujui Izin
        $izins = Izin::with('user')
            ->where(function($q) use ($startDate, $endDate) {
                 $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                   ->orWhereBetween('tanggal_selesai', [$startDate, $endDate]);
            })
            ->whereIn('status', ['approved', 'disetujui'])
            ->get();
            
        $data = collect();
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            
            // Condition: Date is in past OR (Date is Today AND Time > Cutoff)
            
            // Use Asia/Makassar (WITA) for Gorontalo
            $currentTime = Carbon::now('Asia/Makassar');
            
            $isPast = $date->lt($currentTime->copy()->startOfDay());
            $isToday = $date->format('Y-m-d') === $currentTime->format('Y-m-d');
            $isLateToday = $isToday && $currentTime->format('H:i:s') > $cutoffTime;
            
            $shouldCheckAbsence = $isPast || $isLateToday;

            foreach ($users as $user) {
                // 1. Check Existing Absensi
                $att = $absensis->first(function($item) use ($user, $dateStr) {
                    return $item->user_id == $user->id && 
                           \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') === $dateStr;
                });
                if ($att) {
                    $item = new \stdClass();
                    $item->id = $att->id; 
                    $item->user = $user;
                    $item->tanggal = $att->tanggal;
                    $item->waktu_masuk = $att->waktu_masuk;
                    $item->waktu_keluar = $att->waktu_keluar;
                    $item->status = $this->formatStatus($att->status);
                    $item->keterangan = $att->keterangan;
                    $item->catatan = null; // Default property
                    $data->push($item);
                    continue;
                }
                
                // 2. Check Izin
                $perm = $izins->first(function($i) use ($dateStr, $user) {
                    return $i->user_id == $user->id && 
                           \Carbon\Carbon::parse($i->tanggal_mulai)->format('Y-m-d') <= $dateStr && 
                           \Carbon\Carbon::parse($i->tanggal_selesai)->format('Y-m-d') >= $dateStr;
                });
                
                if ($perm) {
                   // Hide future Izin matching the user request (only show up to today)
                   if (!$isPast && !$isToday) {
                       continue;
                   }

                   $item = new \stdClass();
                   $item->id = 'izin_' . $perm->id . '_' . $dateStr;
                   $item->user = $user;
                   $item->tanggal = $dateStr;
                   $item->waktu_masuk = '-';
                   $item->waktu_keluar = '-';
                   $item->status = 'Izin' . ($perm->catatan ? ' (' . $perm->catatan . ')' : '');
                   $item->keterangan = $perm->keterangan;
                   $item->tanggal_mulai = $perm->tanggal_mulai;
                   $item->tanggal_selesai = $perm->tanggal_selesai;
                   $item->catatan = $perm->catatan; // Renamed from kategori_izin
                   $data->push($item);
                   continue;
                }
                
                // 3. Mark as Absent if applicable
                if ($shouldCheckAbsence) {
                   $item = new \stdClass();
                   $item->id = 'alpha_' . $user->id . '_' . $dateStr;
                   $item->user = $user;
                   $item->tanggal = $dateStr;
                   $item->waktu_masuk = '-';
                   $item->waktu_keluar = '-';
                   $item->status = 'Tidak Hadir (Alpha)';
                   $item->keterangan = '-';
                   $item->catatan = null; // Default property
                   $data->push($item);
                }
            }
        }
        
        return $data->sortBy([
            ['tanggal', 'desc'],
            ['user.name', 'asc'],
        ])->values();
    }
    
    private function formatStatus($status) {
        if (!$status) return 'Belum Absen';
        if (stripos($status, 'hadir') !== false) return 'Hadir';
        if (stripos($status, 'terlambat') !== false) return 'Terlambat';
        return ucfirst($status);
    }
}