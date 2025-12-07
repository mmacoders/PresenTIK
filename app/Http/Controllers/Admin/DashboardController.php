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
            
        // Get leave requests for today
        $todayLeaveRequests = Izin::where('tanggal_mulai', '<=', $today)
          ->where('tanggal_selesai', '>=', $today)
          ->get();
        
        // Calculate statistics
        $totalUsers = $users->count();
        $presentToday = $todayAttendances->whereNotNull('waktu_masuk')->count();
        $lateToday = $todayAttendances->filter(function ($attendance) use ($jamMasuk) {
            // Check if late based on system settings
            return $attendance->waktu_masuk && $attendance->waktu_masuk > $jamMasuk;
        })->count();
        
        // Calculate leave count based on unique users on leave today
        $leaveToday = $todayLeaveRequests->unique('user_id')->count();
        
        // Absent is total users minus present minus leave (assuming leave implies not present, though they might overlap)
        // A better way is to count users who are neither present nor on leave
        $usersPresentIds = $todayAttendances->pluck('user_id')->toArray();
        $usersOnLeaveIds = $todayLeaveRequests->pluck('user_id')->toArray();
        
        $absentToday = $users->whereNotIn('id', $usersPresentIds)
                             ->whereNotIn('id', $usersOnLeaveIds)
                             ->count();
        
        // Prepare data for the attendance table - Include ALL users
        $attendanceData = $this->prepareAttendanceData($users, $todayAttendances, $todayLeaveRequests, $jamMasuk);
        
        // Get today's attendance for the admin
        $adminAttendance = Absensi::where('user_id', $admin->id)
            ->where('tanggal', $today)
            ->first();
            
        // Get today's leave for the admin
        $adminIzin = Izin::where('user_id', $admin->id)
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();
        
        return Inertia::render('Admin/Dashboard', [
            'admin' => $admin,
            'todayAttendance' => $adminAttendance,
            'todayIzin' => $adminIzin,
            'stats' => [
                'present' => $presentToday,
                'late' => $lateToday,
                'leave' => $leaveToday,
                'absent' => $absentToday,
            ],
            'attendanceData' => $attendanceData,
        ]);
    }
    
    private function prepareAttendanceData($users, $attendances, $leaveRequests, $jamMasuk)
    {
        $data = [];
        
        foreach ($users as $user) {
            $attendance = $attendances->firstWhere('user_id', $user->id);
            $leave = $leaveRequests->firstWhere('user_id', $user->id);
            
            // Skip users who have not attended and are not on leave
            if (!$attendance && !$leave) {
                continue;
            }
            
            $checkIn = '-';
            $checkOut = '-';
            $status = 'Tidak Hadir';
            $keterangan = '-';
            
            if ($attendance) {
                $checkIn = $attendance->waktu_masuk ? Carbon::parse($attendance->waktu_masuk)->format('H:i:s') : '-';
                $checkOut = $attendance->waktu_keluar ? Carbon::parse($attendance->waktu_keluar)->format('H:i:s') : '-';
                $status = $this->determineStatus($attendance, $leave, $jamMasuk);
                $keterangan = $attendance->keterangan ?? '-';
            } elseif ($leave) {
                $status = 'Izin';
                if ($leave->jenis_izin === 'sakit') $status = 'Sakit';
                if ($leave->jenis_izin === 'cuti') $status = 'Cuti';
                if ($leave->jenis_izin === 'parsial') $status = 'Izin Parsial';
                $keterangan = $leave->alasan ?? '-';
            }
            
            $data[] = [
                'name' => $user->name,
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'status' => $status,
                'keterangan' => $keterangan,
            ];
        }
        
        // Sort data: Hadir/Terlambat first, then Izin
        usort($data, function ($a, $b) {
            $statusOrder = [
                'Hadir' => 1,
                'Terlambat' => 2,
                'Izin' => 3,
                'Sakit' => 3,
                'Cuti' => 3,
                'Izin Parsial' => 3,
                'Tidak Hadir' => 4
            ];
            
            // Helper to get order
            $getOrder = function($status) use ($statusOrder) {
                foreach ($statusOrder as $key => $val) {
                    if (strpos($status, $key) === 0) return $val;
                }
                return 4; // Default to last
            };
            
            return $getOrder($a['status']) <=> $getOrder($b['status']);
        });
        
        return $data;
    }
    
    private function determineStatus($attendance, $leave, $jamMasuk)
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
        if ($leave) {
            // Handle partial leave specifically
            if ($leave->jenis_izin === 'parsial') {
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
            
            if ($leave->jenis_izin === 'sakit') return 'Sakit';
            if ($leave->jenis_izin === 'cuti') return 'Cuti';
            
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