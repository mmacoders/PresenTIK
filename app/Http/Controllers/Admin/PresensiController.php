<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\SystemSetting;
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
        
        // Get system settings
        $systemSettings = SystemSetting::first();
        
        return Inertia::render('Admin/Presensi', [
            'todayAttendance' => $todayAttendance,
            'recentAttendance' => $recentAttendance,
            'systemSettings' => $systemSettings,
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

        // Validate location
        $locationValidationDisabled = $request->boolean('disable_validation');
        
        if (!$locationValidationDisabled) {
            $request->validate([
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);
            
            if ($this->validateLocation($request->lat, $request->lng) !== 'valid') {
                return back()->with('error', 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.');
            }
        } else {
            $request->validate([
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
            ]);
        }

        // Check presensi start time
        $settings = SystemSetting::first();
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
            'lat_masuk' => $request->lat ?? 0,
            'lng_masuk' => $request->lng ?? 0,
            'status_lokasi_masuk' => $locationValidationDisabled ? 'valid' : 'valid', // Always valid if disabled or passed validation
            'status' => 'Hadir',
        ]);
        
        return back()->with('success', 'Presensi berhasil dicatat');
    }

    // Validate user location against office location
    private function validateLocation($lat, $lng)
    {
        // Get system settings
        $settings = SystemSetting::first();
        
        if (!$settings) {
            return 'invalid';
        }
        
        // Get office location from settings
        $officeLat = $settings->location_latitude;
        $officeLng = $settings->location_longitude;
        $radius = $settings->location_radius;
        
        // Validate coordinates
        if (!is_numeric($lat) || !is_numeric($lng) || !is_numeric($officeLat) || !is_numeric($officeLng) || !is_numeric($radius)) {
            return 'invalid';
        }
        
        // Convert to radians
        $radLat1 = (pi() * $lat) / 180;
        $radLng1 = (pi() * $lng) / 180;
        $radLat2 = (pi() * $officeLat) / 180;
        $radLng2 = (pi() * $officeLng) / 180;
        
        // Haversine formula
        $deltaLat = $radLat2 - $radLat1;
        $deltaLng = $radLng2 - $radLng1;
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($radLat1) * cos($radLat2) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        // Earth's radius in meters
        $earthRadius = 6371000;
        
        // Calculate distance
        $distance = $earthRadius * $c;
        
        // Valid if within specified radius
        return $distance <= $radius ? 'valid' : 'invalid';
    }
}
