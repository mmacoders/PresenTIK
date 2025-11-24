<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LaporanController extends Controller
{
    public function index()
    {
        $admin = auth()->user();
        
        // Get date range (default to current month)
        $startDate = request('start_date', date('Y-m-01'));
        $endDate = request('end_date', date('Y-m-t'));
        
        // Get all users (since we're not using bidang anymore)
        $users = User::where('role', 'user')
                    ->get();
        
        // Get attendance records for the date range
        $attendances = Absensi::whereHas('user', function ($query) {
                        $query->where('role', 'user');
                    })
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->with('user')
                    ->orderBy('tanggal', 'desc')
                    ->get();
        
        return Inertia::render('Admin/Laporan', [
            'users' => $users,
            'attendances' => $attendances,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
    
    public function export(Request $request)
    {
        $admin = auth()->user();
        
        // Get date range
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        // Get attendance records for export
        $attendances = Absensi::whereHas('user', function ($query) {
                        $query->where('role', 'user');
                    })
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->with('user')
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('user.name')
                    ->get();
        
        // Create CSV content
        $csvData = "Nama,Tanggal,Masuk,Keluar,Status,Keterangan\n";
        
        foreach ($attendances as $attendance) {
            $csvData .= '"' . $attendance->user->name . '",';
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