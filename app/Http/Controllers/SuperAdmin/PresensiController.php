<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\SystemSetting;
use Carbon\Carbon;
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
        // Get recent attendance (last 30 days combined with Izin)
        $startWindow = now()->subDays(30);

        $presensi = Absensi::where('user_id', $user->id)
            ->where('tanggal', '>=', $startWindow)
            ->get()
            ->toBase()
            ->map(function ($item) {
                return [
                    'id' => 'presensi_' . $item->id,
                    'tanggal' => $item->tanggal,
                    'date_display' => Carbon::parse($item->tanggal)->translatedFormat('d M Y'),
                    'waktu_masuk' => $item->waktu_masuk ?? '-',
                    'status' => $item->status,
                ];
            });

        $izin = \App\Models\Izin::where('user_id', $user->id)
            ->where('tanggal_mulai', '>=', $startWindow)
            ->get()
            ->toBase()
            ->map(function ($item) {
                $statusFormatted = $item->catatan ?? 'Izin';
                if ($item->status === 'pending') {
                    $statusFormatted .= ' (Menunggu)';
                } elseif ($item->status === 'rejected') {
                    $statusFormatted .= ' (Ditolak)';
                } elseif ($item->status === 'approved' || $item->status === 'disetujui') {
                    $statusFormatted .= ' (Disetujui)';
                }
                
                $start = Carbon::parse($item->tanggal_mulai)->translatedFormat('d M');
                $end = Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y');
                
                if ($item->tanggal_mulai === $item->tanggal_selesai) {
                    $dateDisplay = Carbon::parse($item->tanggal_mulai)->translatedFormat('d M Y');
                } else {
                    $dateDisplay = "$start - $end";
                }
                
                return [
                    'id' => 'izin_' . $item->id,
                    'tanggal' => $item->tanggal_mulai,
                    'date_display' => $dateDisplay,
                    'waktu_masuk' => '-',
                    'status' => $statusFormatted,
                ];
            });

        $recentAttendance = $presensi->merge($izin)->sortByDesc('tanggal')->take(7)->values();
        
        // Get system settings
        $systemSettings = SystemSetting::first();
        
        // Get today's leave
        $todayIzin = \App\Models\Izin::where('user_id', $user->id)
            ->where('tanggal_mulai', '<=', today())
            ->where('tanggal_selesai', '>=', today())
            ->first();
        
        return Inertia::render('SuperAdmin/Presensi', [
            'todayAttendance' => $todayAttendance,
            'todayIzin' => $todayIzin,
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
        
        // Determine status based on time
        $jamMasuk = $settings->jam_masuk ?? '08:00:00';
        $gracePeriodMinutes = $settings->grace_period_minutes ?? 10;
        
        // Calculate grace period end time
        $gracePeriodEnd = Carbon::parse($jamMasuk)->addMinutes($gracePeriodMinutes)->format('H:i:s');
        
        $status = 'Hadir';
        if ($currentTime > $gracePeriodEnd) {
            $status = 'Terlambat';
            $request->validate([
                'keterangan' => 'required|string|max:255',
            ]);
        }

        // Create attendance record
        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => today(),
            'waktu_masuk' => $currentTime,
            'lat_masuk' => $request->lat ?? 0,
            'lng_masuk' => $request->lng ?? 0,
            'status_lokasi_masuk' => $locationValidationDisabled ? 'valid' : 'valid', // Always valid if disabled or passed validation
            'status' => $status,
            'keterangan' => $request->keterangan,
        ]);
        
        return back()->with('success', 'Presensi berhasil dicatat');
    }

    // Request permission for superadmin
    public function requestPermission(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'jenis_izin' => 'required|in:penuh',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
            'catatan' => 'required|string|in:Izin,Sakit,Cuti,Lainnya',
        ]);

        // Check for overlapping leave requests
        // Check for permission with same end date as requested by user
        // "izin di tanggal selesai yang sama itu tidak boleh"
        $sameEndDate = \App\Models\Izin::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->where('tanggal_selesai', $request->tanggal_selesai)
            ->exists();

        if ($sameEndDate) {
            return back()->with('error', 'Anda sudah memiliki izin dengan tanggal selesai yang sama (' . Carbon::parse($request->tanggal_selesai)->translatedFormat('d F Y') . ').');
        }
        
        // Handle file upload
        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $user->id . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('izin_files', $fileName, 'public');
        }
        
        \App\Models\Izin::create([
            'user_id' => $user->id,
            'tanggal' => $request->tanggal_mulai,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jenis_izin' => $request->jenis_izin,
            'keterangan' => $request->keterangan,
            'disetujui_oleh' => null,
            'status' => 'pending',
            'catatan' => $request->catatan,
            'file_path' => $filePath,
        ]);
        
        return back()->with('success', 'Permintaan izin berhasil diajukan.');
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
