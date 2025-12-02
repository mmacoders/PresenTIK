<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Izin;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

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
        
        // Get all users including admin and superadmin for monitoring
        $users = User::all();
        
        // --- Attendances Query ---
        $attendancesQuery = Absensi::whereBetween('tanggal', [$startDate, $endDate])
                    ->with('user')
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('created_at', 'desc');

        if ($search) {
            $attendancesQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
        
        $attendances = $attendancesQuery->paginate(10, ['*'], 'attendances_page')->withQueryString();

        // --- Permissions (Izin) Query ---
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
        $admin = auth()->user();
        
        // Get date range
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $search = $request->search;
        
        // Get attendance records for export (all roles)
        $attendancesQuery = Absensi::whereBetween('tanggal', [$startDate, $endDate])
                    ->with('user')
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('created_at', 'desc');

        if ($search) {
            $attendancesQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
        
        $attendances = $attendancesQuery->get();
        
        // Create CSV content with Role column
        $csvData = "Nama,Role,Tanggal,Masuk,Keluar,Status,Keterangan\n";
        
        foreach ($attendances as $attendance) {
            if (!$attendance->user) continue; // Skip if user not found
            
            $csvData .= '"' . $attendance->user->name . '",';
            $csvData .= '"' . ucfirst($attendance->user->role) . '",';
            $csvData .= '"' . $attendance->tanggal . '",';
            $csvData .= '"' . ($attendance->waktu_masuk ?? '-') . '",';
            $csvData .= '"' . ($attendance->waktu_keluar ?? '-') . '",';
            $csvData .= '"' . $this->getStatusText($attendance) . '",';
            $csvData .= '"' . ($attendance->keterangan ?? '-') . '"' . "\n";
        }
        
        // Return CSV download
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="laporan-absensi.csv"');
    }
    
    // Helper function to get status text
    private function getStatusText($attendance)
    {
        // Handle all possible status values from the database
        switch ($attendance->status) {
            case 'izin':
            case 'Izin':
            case 'Izin (Valid)':
                return 'Izin';
            case 'sakit':
                return 'Sakit';
            case 'terlambat':
            case 'Terlambat':
                return 'Terlambat';
            case 'hadir':
            case 'Hadir':
                return $attendance->waktu_masuk ? 'Hadir' : 'Belum Absen';
            case 'Izin Parsial (Check-in)':
                return 'Izin Parsial (Check-in)';
            case 'Izin Parsial (Selesai)':
                return 'Izin Parsial (Selesai)';
            default:
                // Handle any other status values or null status
                if ($attendance->waktu_masuk && $attendance->waktu_keluar) {
                    return 'Hadir';
                } else if ($attendance->waktu_masuk) {
                    return 'Sudah Check-in';
                } else {
                    return $attendance->status ?: 'Belum Absen';
                }
        }
    }
}