<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get system settings for working hours
        $systemSettings = SystemSetting::first();
        $jamMasuk = $systemSettings ? $systemSettings->jam_masuk : '08:00:00';
        
        // Get statistics
        $totalUsers = User::where('role', 'user')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        
        // Get today's attendance summary
        $todayAttendances = Absensi::where('tanggal', date('Y-m-d'))->get();
        $presentToday = $todayAttendances->whereNotNull('waktu_masuk')->count();
        $lateToday = $todayAttendances->filter(function ($attendance) use ($jamMasuk) {
            // Check if late based on system settings
            return $attendance->waktu_masuk && $attendance->waktu_masuk > $jamMasuk;
        })->count();
        $absentToday = $totalUsers - $presentToday;
        
        // Get attendance by jabatan (instead of bidang)
        $jabatanStats = $this->getJabatanStats();
        
        // Get weekly attendance data for charts
        $weeklyAttendance = $this->getWeeklyAttendanceData();
        
        // Get today's attendance for the superadmin
        $superAdminAttendance = Absensi::where('user_id', auth()->id())
            ->where('tanggal', date('Y-m-d'))
            ->first();
            
        // Get today's leave for the superadmin
        $superAdminIzin = \App\Models\Izin::where('user_id', auth()->id())
            ->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        return Inertia::render('SuperAdmin/Dashboard', [
            'todayAttendance' => $superAdminAttendance,
            'todayIzin' => $superAdminIzin,
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'presentToday' => $presentToday,
            'lateToday' => $lateToday,
            'absentToday' => $absentToday,
            'jabatanStats' => $jabatanStats,
            'weeklyAttendance' => $weeklyAttendance,
        ]);
    }
    
    private function getJabatanStats()
    {
        // Get all users grouped by jabatan
        $users = User::where('role', 'user')->get();
        
        $jabatanGroups = [];
        foreach ($users as $user) {
            $jabatan = $user->jabatan ?? 'Belum Diatur';
            
            if (!isset($jabatanGroups[$jabatan])) {
                $jabatanGroups[$jabatan] = [
                    'name' => $jabatan,
                    'total_users' => 0,
                    'present_today' => 0
                ];
            }
            
            $jabatanGroups[$jabatan]['total_users']++;
            
            // Check if user is present today
            $todayAttendance = Absensi::where('user_id', $user->id)
                ->where('tanggal', date('Y-m-d'))
                ->whereNotNull('waktu_masuk')
                ->first();
                
            if ($todayAttendance) {
                $jabatanGroups[$jabatan]['present_today']++;
            }
        }
        
        return array_values($jabatanGroups);
    }
    
    private function getWeeklyAttendanceData()
    {
        $dates = [];
        $presentData = [];
        $izinData = [];
        $absentData = [];
        
        // Get total users for absent calculation
        $totalUsers = User::where('role', 'user')->count();
        
        // Get data for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $formattedDate = $date->format('Y-m-d');
            $displayDate = $date->format('d M');
            
            $dates[] = $displayDate;
            
            // Get present count for this date
            $presentCount = Absensi::where('tanggal', $formattedDate)
                                 ->whereNotNull('waktu_masuk')
                                 ->count();
            
            // Get izin count for this date
            $izinCount = Absensi::where('tanggal', $formattedDate)
                                ->where(function($query) {
                                    $query->where('status', 'like', '%Izin%')
                                          ->orWhere('status', 'like', '%izin%');
                                })
                                ->count();
            
            $presentData[] = $presentCount;
            $izinData[] = $izinCount;
            
            // Absent is total - present - izin
            // Ensure we don't go below zero
            $absentCount = max(0, $totalUsers - $presentCount - $izinCount);
            $absentData[] = $absentCount;
        }
        
        return [
            'dates' => $dates,
            'present' => $presentData,
            'izin' => $izinData,
            'absent' => $absentData
        ];
    }
}