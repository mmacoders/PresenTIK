<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        
        // Get system settings for working hours
        $systemSettings = SystemSetting::first();
        $jamMasuk = $systemSettings ? $systemSettings->jam_masuk : '08:00:00';
        
        // Get today's date using Carbon
        $today = Carbon::now()->format('Y-m-d');
        
        // Get all users (since we're removing bidang filter)
        $users = User::where('role', 'user')->get();
        
        // Get today's attendance for all users
        $todayAttendances = Absensi::where('tanggal', $today)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate statistics
        $totalUsers = $users->count();
        $presentToday = $todayAttendances->whereNotNull('waktu_masuk')->count();
        $lateToday = $todayAttendances->filter(function ($attendance) use ($jamMasuk) {
            // Check if late based on system settings
            return $attendance->waktu_masuk && $attendance->waktu_masuk > $jamMasuk;
        })->count();
        $absentToday = $totalUsers - $presentToday;
        
        // Get leave requests for today
        $leaveToday = Izin::where('tanggal_mulai', '<=', $today)
          ->where('tanggal_selesai', '>=', $today)
          ->count();
        
        // Prepare data for the attendance table - ONLY users with attendance records
        $attendanceData = $this->prepareAttendanceData($todayAttendances, $jamMasuk);
        
        return Inertia::render('Admin/Dashboard', [
            'admin' => $admin,
            'stats' => [
                'present' => $presentToday,
                'late' => $lateToday,
                'leave' => $leaveToday,
                'absent' => $absentToday,
            ],
            'attendanceData' => $attendanceData,
        ]);
    }
    
    private function prepareAttendanceData($attendances, $jamMasuk)
    {
        $data = [];
        
        // Only loop through attendances (users who have checked in today)
        foreach ($attendances as $attendance) {
            if (!$attendance->user) continue; // Skip if user not found
            
            $data[] = [
                'name' => $attendance->user->name,
                'checkIn' => $attendance->waktu_masuk ? Carbon::parse($attendance->waktu_masuk)->format('H:i:s') : '-',
                'checkOut' => $attendance->waktu_keluar ? Carbon::parse($attendance->waktu_keluar)->format('H:i:s') : '-',
                'status' => $this->determineStatus($attendance, $attendance->user, $jamMasuk),
                'keterangan' => $attendance->keterangan ?? '-',
            ];
        }
        
        return $data;
    }
    
    private function determineStatus($attendance, $user, $jamMasuk)
    {
        // First check if there's a specific status in the attendance record
        if ($attendance && $attendance->status) {
            // Handle partial leave statuses
            if ($attendance->status === 'Izin Parsial (Check-in)' || $attendance->status === 'Izin Parsial (Selesai)') {
                return $attendance->status;
            }
            
            // Handle other specific statuses
            switch ($attendance->status) {
                case 'Izin (Valid)':
                case 'izin':
                case 'Izin':
                    return 'Izin';
                case 'sakit':
                    return 'Sakit';
                case 'terlambat':
                case 'Terlambat':
                    // Calculate how late the user is
                    if ($attendance->waktu_masuk && $jamMasuk) {
                        $checkInTime = strtotime($attendance->waktu_masuk);
                        $scheduledTime = strtotime($jamMasuk);
                        $differenceSeconds = $checkInTime - $scheduledTime;
                        $differenceMinutes = floor($differenceSeconds / 60);
                        
                        // Format the late time difference
                        if ($differenceMinutes < 60) {
                            $lateText = "{$differenceMinutes} menit";
                        } else {
                            $hours = floor($differenceMinutes / 60);
                            $minutes = $differenceMinutes % 60;
                            $lateText = $minutes > 0 ? "{$hours} jam {$minutes} menit" : "{$hours} jam";
                        }
                        
                        return "Terlambat ({$lateText})";
                    }
                    return 'Terlambat';
                case 'hadir':
                case 'Hadir':
                    return 'Hadir';
                case 'alpha':
                    return 'Tidak Hadir';
            }
        }
        
        // Check if user has an active leave request
        $todayIzin = Izin::where('user_id', $user->id)
            ->where('tanggal_mulai', '<=', Carbon::now()->format('Y-m-d'))
            ->where('tanggal_selesai', '>=', Carbon::now()->format('Y-m-d'))
            ->first();
            
        if ($todayIzin) {
            // Handle partial leave specifically
            if ($todayIzin->jenis_izin === 'parsial') {
                // If we have attendance data, check its status
                if ($attendance) {
                    if ($attendance->waktu_masuk && !$attendance->waktu_keluar) {
                        return 'Izin Parsial (Check-in)';
                    } else if ($attendance->waktu_masuk && $attendance->waktu_keluar) {
                        return 'Izin Parsial (Selesai)';
                    }
                }
                // Default for partial leave without attendance data
                return 'Izin Parsial';
            }
            return 'Izin';
        }
        
        if (!$attendance) {
            return 'Tidak Hadir';
        }
        
        if ($attendance->waktu_masuk) {
            // Check if late (after jam_masuk from system settings)
            if ($attendance->waktu_masuk > $jamMasuk) {
                // Calculate how late the user is
                $checkInTime = strtotime($attendance->waktu_masuk);
                $scheduledTime = strtotime($jamMasuk);
                $differenceSeconds = $checkInTime - $scheduledTime;
                $differenceMinutes = floor($differenceSeconds / 60);
                
                // Format the late time difference
                if ($differenceMinutes < 60) {
                    $lateText = "{$differenceMinutes} menit";
                } else {
                    $hours = floor($differenceMinutes / 60);
                    $minutes = $differenceMinutes % 60;
                    $lateText = $minutes > 0 ? "{$hours} jam {$minutes} menit" : "{$hours} jam";
                }
                
                return "Terlambat ({$lateText})";
            }
            return 'Hadir';
        }
        
        return 'Tidak Hadir';
    }
}