<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PresensiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get today's attendance
        $todayAttendance = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();
        
        // Get recent attendance (last 7 days)
        $recentAttendance = Absensi::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->limit(7)
            ->get();
        
        return Inertia::render('Admin/Presensi', [
            'todayAttendance' => $todayAttendance,
            'recentAttendance' => $recentAttendance,
        ]);
    }

    public function checkin(Request $request)
    {
        $user = auth()->user();
        
        // Check if already checked in today
        $existingAttendance = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();
        
        if ($existingAttendance) {
            return back()->with('error', 'Anda sudah melakukan presensi hari ini');
        }

        // Check presensi start time
        $settings = \App\Models\SystemSetting::first();
        $presensiStartTime = $settings->presensi_start_time ?? '06:00:00';
        $currentTime = now()->format('H:i:s');

        if ($currentTime < $presensiStartTime) {
            return back()->with('error', 'Belum waktunya presensi. Presensi dimulai pukul ' . substr($presensiStartTime, 0, 5));
        }
        
        // Create attendance record
        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => today(),
            'waktu_masuk' => $currentTime,
            'status' => 'Hadir',
        ]);
        
        return back()->with('success', 'Presensi berhasil dicatat');
    }
}
