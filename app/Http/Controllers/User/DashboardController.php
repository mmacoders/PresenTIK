<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Get today's attendance record if exists
        $todayAttendance = $user->absensis()
            ->where('tanggal', date('Y-m-d'))
            ->first();
        
        // Get last 7 days of attendance records
        $attendanceHistory = $user->absensis()
            ->where('tanggal', '>=', date('Y-m-d', strtotime('-7 days')))
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Check if user has leave permission for today
        // Using date range query with tanggal_mulai and tanggal_selesai
        $todayIzin = $user->izins()
            ->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        return Inertia::render('User/Dashboard', [
            'user' => $user,
            'todayAttendance' => $todayAttendance,
            'todayIzin' => $todayIzin,
            'attendanceHistory' => $attendanceHistory,
        ]);
    }
}