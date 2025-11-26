<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Get current date using Carbon
        $today = Carbon::now()->format('Y-m-d');
        $sevenDaysAgo = Carbon::now()->subDays(7)->format('Y-m-d');
        
        // Get today's attendance record if exists
        $todayAttendance = $user->absensis()
            ->where('tanggal', $today)
            ->first();
        
        // Get last 7 days of attendance records
        $attendanceHistory = $user->absensis()
            ->where('tanggal', '>=', $sevenDaysAgo)
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Get last 7 days of leave/izin records
        $izinHistory = $user->izins()
            ->where(function($query) use ($sevenDaysAgo, $today) {
                // Get izin that overlaps with the last 7 days
                $query->where(function($q) use ($sevenDaysAgo, $today) {
                    $q->whereBetween('tanggal_mulai', [$sevenDaysAgo, $today])
                      ->orWhereBetween('tanggal_selesai', [$sevenDaysAgo, $today])
                      ->orWhere(function($q2) use ($sevenDaysAgo, $today) {
                          $q2->where('tanggal_mulai', '<=', $sevenDaysAgo)
                             ->where('tanggal_selesai', '>=', $today);
                      });
                });
            })
            ->orderBy('tanggal_mulai', 'desc')
            ->get();
        
        // Check if user has leave permission for today
        $todayIzin = $user->izins()
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();
        
        return Inertia::render('User/Dashboard', [
            'user' => $user,
            'todayAttendance' => $todayAttendance,
            'todayIzin' => $todayIzin,
            'attendanceHistory' => $attendanceHistory,
            'izinHistory' => $izinHistory, // Add izin history
        ]);
    }
}