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
        
        // Manual Pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage('attendances_page');
        $perPage = 10;
        $currentItems = $reportData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $attendances = new LengthAwarePaginator(
            $currentItems, 
            $reportData->count(), 
            $perPage, 
            $currentPage, 
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'attendances_page',
            ]
        );
        $attendances->appends($request->all());

        // --- Permissions (Izin) Query (Maintained for separate tab) ---
        // Keeps original logic for Izin tab
        $permissionsQuery = Izin::with('user')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tanggal_selesai', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc');

        if ($search) {
            $permissionsQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
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
            $isPast = $date->lt(Carbon::now()->startOfDay());
            $isLateToday = $date->isToday() && Carbon::now()->format('H:i:s') > $cutoffTime;
            $shouldCheckAbsence = $isPast || $isLateToday;

            foreach ($users as $user) {
                // 1. Check Existing Absensi
                $att = $absensis->where('user_id', $user->id)->where('tanggal', $dateStr)->first();
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
                $perm = $izins->filter(function($i) use ($dateStr, $user) {
                    return $i->user_id == $user->id && 
                           $i->tanggal_mulai <= $dateStr && 
                           $i->tanggal_selesai >= $dateStr;
                })->first();
                
                if ($perm) {
                   $item = new \stdClass();
                   $item->id = 'izin_' . $perm->id . '_' . $dateStr;
                   $item->user = $user;
                   $item->tanggal = $dateStr;
                   $item->waktu_masuk = '-';
                   $item->waktu_keluar = '-';
                   $item->status = 'Izin (' . $perm->catatan . ')';
                   $item->keterangan = $perm->keterangan;
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