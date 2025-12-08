<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth()->user();
        
        // Get date range (default to current month) using Carbon with proper timezone
        $now = Carbon::now();
        $startDate = request('start_date', $now->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', $now->copy()->endOfMonth()->format('Y-m-d'));
        $search = request('search');
        
        // Get all users including admin and superadmin for monitoring column in report
        // (Not used directly here anymore as getAttendanceReportData handles it, 
        // but kept if the view needs a list of users for dropdowns etc - view uses 'users' prop? Yes.)
        $users = User::all();
        
        // --- Attendances Unified Data ---
        $reportData = $this->getAttendanceReportData($startDate, $endDate, $search);

        // Filter out "Izin" records for the Presensi Tab view
        // The user requested Izin not to show in Presensi tab, effectively showing only Hadir and Alpha
        $displayData = $reportData->filter(function($row) {
            return stripos($row->status, 'Izin') === false;
        });
        
        // Manual Pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage('attendances_page');
        $perPage = 10;
        $currentItems = $displayData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $attendances = new LengthAwarePaginator(
            $currentItems, 
            $displayData->count(), // Count from filtered data 
            $perPage, 
            $currentPage, 
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'attendances_page',
            ]
        );
        $attendances->appends($request->all());

        // --- Permissions (Izin) Query ---
        $permissionsQuery = Izin::with('user')->orderBy('created_at', 'desc');

        // Apply filters only if provided in request, matching SuperAdmin behavior
        // allowing Admin to see all permissions (e.g. future/past) by default or when clearing filters
        if ($request->filled('search')) {
            $permissionsQuery->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('start_date')) {
             $permissionsQuery->where('tanggal_mulai', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
             $permissionsQuery->where('tanggal_selesai', '<=', $request->end_date);
        }

        $permissions = $permissionsQuery->paginate(10, ['*'], 'permissions_page')->withQueryString();
        
        // Set no-cache headers
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        return Inertia::render('Admin/Laporan', [
            'users' => $users,
            'attendances' => $attendances,
            'permissions' => $permissions,
            'filters' => $request->only(['start_date', 'end_date', 'search']),
        ]);
    }
    
    public function export(Request $request)
    {
        // Get date range
        $now = Carbon::now();
        $startDate = $request->start_date ?? $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? $now->copy()->endOfMonth()->format('Y-m-d');
        $search = $request->search;
        $format = $request->format;
        
        $reportData = $this->getAttendanceReportData($startDate, $endDate, $search);

        if ($format === 'excel') {
            return Excel::download(new LaporanExport($reportData), 'laporan-absensi.xlsx');
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pdf.laporan_absensi', [
                'attendances' => $reportData,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            return $pdf->download('laporan-absensi.pdf');
        }
        
        // CSV Fallback
        $csvData = "Nama,Role,Tanggal,Masuk,Keluar,Status,Keterangan\n";
        
        foreach ($reportData as $row) {
            $csvData .= '"' . $row->user->name . '",';
            $csvData .= '"' . ucfirst($row->user->role) . '",';
            $csvData .= '"' . $row->tanggal . '",';
            $csvData .= '"' . $row->waktu_masuk . '",';
            $csvData .= '"' . $row->waktu_keluar . '",';
            $csvData .= '"' . $row->status . '",';
            $csvData .= '"' . $row->keterangan . '"' . "\n";
        }
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="laporan-absensi.csv"');
    }

    private function getAttendanceReportData($startDate, $endDate, $search = null)
    {
        $setting = SystemSetting::first();
        // Default cutoff 17:00:00 if not set
        $cutoffTime = $setting ? ($setting->cutoff_time ?? '17:00:00') : '17:00:00';

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
            
            // Check if we should mark as Absent
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
                    // Standardize object
                    $item = new \stdClass();
                    $item->id = $att->id; // Integer ID
                    $item->user = $user;
                    $item->tanggal = $att->tanggal;
                    $item->waktu_masuk = $att->waktu_masuk;
                    $item->waktu_keluar = $att->waktu_keluar;
                    $item->status = $this->formatStatus($att->status);
                    $item->keterangan = $att->keterangan;
                    $data->push($item);
                    continue;
                }
                
                // 2. Check Izin
                $perm = $izins->first(function($i) use ($dateStr, $user) { // Use first() with closure
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
                   $item->status = 'Izin (' . $perm->catatan . ')';
                   $item->keterangan = $perm->keterangan;
                   $item->tanggal_mulai = $perm->tanggal_mulai;
                   $item->tanggal_selesai = $perm->tanggal_selesai;
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
        // Simple formatter to ensure consistency
        if (!$status) return 'Belum Absen';
        if (stripos($status, 'hadir') !== false) return 'Hadir';
        if (stripos($status, 'terlambat') !== false) return 'Terlambat';
        return ucfirst($status);
    }
}