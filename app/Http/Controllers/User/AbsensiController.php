<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // Show attendance page
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get current date using Carbon
        $today = Carbon::now()->format('Y-m-d');
        
        // Get today's attendance record
        $todayAttendance = $user->absensis()->where('tanggal', $today)->first();
        
        // Get today's leave request
        $todayIzin = $user->izins()->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();
        
        // Get system settings
        $systemSettings = SystemSetting::first();
        
        // Check if testing mode is disabled (either globally or for this session)
        $testingModeDisabled = ($systemSettings && $systemSettings->disable_location_validation) || 
                              session('testing_mode_disabled', false);
        
        // Get attendance history (last 7 days)
        $attendanceHistory = $user->absensis()
            ->where('tanggal', '>=', Carbon::now()->subDays(7)->format('Y-m-d'))
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Get pending leave requests
        $pendingIzins = $user->izins()
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return inertia('User/Absensi', [
            'todayAttendance' => $todayAttendance,
            'todayIzin' => $todayIzin,
            'systemSettings' => $systemSettings,
            'testingModeDisabled' => $testingModeDisabled,
            'attendanceHistory' => $attendanceHistory,
            'pendingIzins' => $pendingIzins,
        ]);
    }
    
    // Process check-in
    public function checkIn(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            Log::info('Check-in attempt', ['user_id' => $user->id, 'request_data' => $request->all()]);
            
            // Check if location validation is disabled (either globally or for testing)
            $systemSettings = SystemSetting::first();
            $locationValidationDisabled = ($systemSettings && $systemSettings->disable_location_validation) || $request->session()->get('testing_mode_disabled', false);
            
            // Validate GPS coordinates only if location validation is enabled
            if (!$locationValidationDisabled) {
                $request->validate([
                    'lat' => 'required|numeric',
                    'lng' => 'required|numeric',
                ]);
            } else {
                // When location validation is disabled, lat and lng are optional
                $request->validate([
                    'lat' => 'nullable|numeric',
                    'lng' => 'nullable|numeric',
                ]);
            }
            
            // Set default values for lat/lng if not provided and location validation is disabled
            $lat = $request->lat ?? 0;
            $lng = $request->lng ?? 0;
            
            // Check if user is within office radius (unless location validation is disabled)
            if (!$locationValidationDisabled) {
                if ($this->validateLocation($lat, $lng) !== 'valid') {
                    Log::warning('Location validation failed', ['user_id' => $user->id, 'lat' => $lat, 'lng' => $lng]);
                    return redirect()->back()->with('error', 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.');
                }
            }
            
            // Check if user can check in
            if (!$this->canCheckIn($user)) {
                Log::warning('User cannot check in', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'Anda tidak diizinkan melakukan check-in hari ini.');
            }
            
            // Get system settings for grace period and cutoff time
            $systemSettings = SystemSetting::first();
            $jamMasuk = $systemSettings->jam_masuk ?? '08:00:00';
            $gracePeriodMinutes = $systemSettings->grace_period_minutes ?? 10;
            $cutoffTime = $systemSettings->cutoff_time ?? '10:00:00';
            $presensiStartTime = $systemSettings->presensi_start_time ?? '06:00:00';
            
            // Get current time using Carbon with proper timezone
            $now = Carbon::now();
            $currentTime = $now->format('H:i:s');
            $currentDate = $now->format('Y-m-d');
            
            // Check if it's too early to check in
            if ($currentTime < $presensiStartTime) {
                return redirect()->back()->with('error', 'Belum waktunya presensi. Presensi dimulai pukul ' . substr($presensiStartTime, 0, 5));
            }
            
            // Calculate grace period end time (jam_masuk + grace_period_minutes)
            $gracePeriodEnd = Carbon::parse($jamMasuk)->addMinutes($gracePeriodMinutes)->format('H:i:s');
            
            // Determine status based on time
            $isLate = $currentTime > $gracePeriodEnd; // Late if after grace period
            $isOnTime = $currentTime <= $gracePeriodEnd && $currentTime >= $jamMasuk; // On time if within grace period
            $isEarly = $currentTime < $jamMasuk; // Early if before jam_masuk
            $isAfterCutoff = $currentTime > $cutoffTime; // Absent if after cutoff time
            
            // Prepare check-in data
            $checkinData = [
                'user_id' => $user->id,
                'tanggal' => $currentDate,
                'waktu_masuk' => $currentTime,
                'lat_masuk' => $lat,
                'lng_masuk' => $lng,
                'status_lokasi_masuk' => $locationValidationDisabled ? 'valid' : $this->validateLocation($lat, $lng),
            ];
            
            // Add keterangan if provided (for late arrivals)
            if ($request->has('keterangan')) {
                $checkinData['keterangan'] = $request->keterangan;
            }
            
            // Determine status
            if ($isAfterCutoff) {
                // If after cutoff time, mark as absent
                $checkinData['status'] = 'Tidak Hadir (Alpha)';
            } elseif ($isLate) {
                // If late but within cutoff time
                $checkinData['status'] = 'Terlambat';
            } elseif ($isOnTime || $isEarly) {
                // If on time or early
                $checkinData['status'] = 'Hadir';
            }
            
            Log::info('Creating attendance record for check-in', ['data' => $checkinData]);
            
            // Use database transaction to ensure data consistency
            DB::transaction(function () use ($checkinData) {
                // Create or update check-in information
                Absensi::updateOrCreate(
                    ['user_id' => $checkinData['user_id'], 'tanggal' => $checkinData['tanggal']],
                    $checkinData
                );
            });
            
            Log::info('Attendance record created for check-in', ['user_id' => $user->id]);
            
            // If after cutoff time, show appropriate message
            if ($isAfterCutoff) {
                return redirect()->back()->with('success', 'Check-in berhasil dicatat. Anda telah ditandai sebagai tidak hadir (alpha) karena check-in dilakukan melewati batas waktu yang ditentukan.');
            }
            
            return redirect()->back()->with('success', 'Check-in berhasil dicatat.');
        } catch (\Exception $e) {
            Log::error('Check-in error', ['user_id' => Auth::id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan check-in. Silakan coba lagi.');
        }
    }
    
    // Process permission/leave request
    public function requestPermission(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'jenis_izin' => 'required|in:penuh', // Only full leave is now supported
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
        ]);

        // Check for permission with same end date as requested by user
        // "izin di tanggal selesai yang sama itu tidak boleh"
        $sameEndDate = Izin::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->where('tanggal_selesai', $request->tanggal_selesai)
            ->exists();

        if ($sameEndDate) {
            return redirect()->back()->with('error', 'Anda sudah memiliki izin dengan tanggal selesai yang sama (' . Carbon::parse($request->tanggal_selesai)->translatedFormat('d F Y') . ').');
        }
        
        try {
            // Handle file upload for full leave requests
            $filePath = null;
            if ($request->hasFile('file') && $request->jenis_izin === 'penuh') {
                $file = $request->file('file');
                $fileName = time() . '_' . $user->id . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('izin_files', $fileName, 'public');
            }
            
            // Create izin record
            $izin = Izin::create([
                'user_id' => $user->id,
                'tanggal' => $request->tanggal_mulai, // For backward compatibility
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jenis_izin' => $request->jenis_izin,
                'keterangan' => $request->keterangan,
                'disetujui_oleh' => null,
                'status' => 'pending',
                'catatan' => $request->catatan,
                'file_path' => $filePath, // Add file path if uploaded
            ]);
            
            Log::info('Permission request created', ['izin_id' => $izin->id, 'user_id' => $user->id]);
            
            return redirect()->back()->with('success', 'Permintaan izin berhasil diajukan dan sedang menunggu persetujuan.');
        } catch (\Exception $e) {
            Log::error('Permission request error', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengajukan izin. Silakan coba lagi.');
        }
    }
    
    // Check if user can check in
    private function canCheckIn(User $user)
    {
        $today = Carbon::now()->format('Y-m-d');
        
        // Check if user already has an attendance record for today with check-in time
        $todayAttendance = $user->absensis()->where('tanggal', $today)->first();
        
        // If user already checked in, they can't check in again
        if ($todayAttendance && $todayAttendance->waktu_masuk) {
            return false;
        }
        
        // Check if user has full leave permission for today
        $todayIzin = $user->izins()->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();
            
        if ($todayIzin && $todayIzin->jenis_izin === 'penuh') {
            return false; // User with full leave can't check in
        }
        
        return true;
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
    
    // Mark absent users (for admin/superadmin)
    public function markAbsentUsers()
    {
        try {
            // Get all users
            $users = User::where('role', 'user')->get();
            
            // Get today's date
            $today = date('Y-m-d');
            
            // Process each user
            foreach ($users as $user) {
                // Check if user already has an attendance record for today
                $attendance = $user->absensis()->where('tanggal', $today)->first();
                
                // If no attendance record, create one with "Tidak Hadir" status
                if (!$attendance) {
                    Absensi::create([
                        'user_id' => $user->id,
                        'tanggal' => $today,
                        'status' => 'Tidak Hadir (Alpha)',
                        'keterangan' => 'Tidak melakukan absensi',
                    ]);
                }
            }
            
            return redirect()->back()->with('success', 'Berhasil menandai pengguna yang tidak hadir.');
        } catch (\Exception $e) {
            Log::error('Mark absent users error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menandai pengguna yang tidak hadir.');
        }
    }
}