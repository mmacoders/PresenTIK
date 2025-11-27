<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    public function index()
    {
        // Get the first (and only) system settings record
        $settings = SystemSetting::first();
        
        // If no settings exist, create a default one
        if (!$settings) {
            $settings = SystemSetting::create([
                'location_latitude' => 0.52400050,
                'location_longitude' => 123.06047523,
                'location_radius' => 100,
                'disable_location_validation' => false,
                'jam_masuk' => '08:00:00',
                'grace_period_minutes' => 10,
                'cutoff_time' => '10:00:00',
            ]);
        }
        
        return response()->json($settings);
    }
    
    public function update(Request $request)
    {
        // Log the incoming request data
        Log::info('System settings update request', ['data' => $request->all()]);
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'location_latitude' => 'required|numeric|between:-90,90',
            'location_longitude' => 'required|numeric|between:-180,180',
            'location_radius' => 'required|integer|min:10|max:1000',
            'disable_location_validation' => 'boolean',
            'jam_masuk' => 'required|string|regex:/^\d{2}:\d{2}(:\d{2})?$/',
            'presensi_start_time' => 'required|string|regex:/^\d{2}:\d{2}(:\d{2})?$/',
            'grace_period_minutes' => 'required|integer|min:0|max:60',
            'cutoff_time' => 'required|string|regex:/^\d{2}:\d{2}(:\d{2})?$/',
        ], [
            'location_latitude.required' => 'Latitude wajib diisi.',
            'location_latitude.numeric' => 'Latitude harus berupa angka.',
            'location_latitude.between' => 'Latitude harus antara -90 dan 90.',
            'location_longitude.required' => 'Longitude wajib diisi.',
            'location_longitude.numeric' => 'Longitude harus berupa angka.',
            'location_longitude.between' => 'Longitude harus antara -180 dan 180.',
            'location_radius.required' => 'Radius wajib diisi.',
            'location_radius.integer' => 'Radius harus berupa angka bulat.',
            'location_radius.min' => 'Radius minimal 10 meter.',
            'location_radius.max' => 'Radius maksimal 1000 meter.',
            'disable_location_validation.boolean' => 'Nilai validasi lokasi harus berupa boolean.',
            'jam_masuk.required' => 'Jam masuk wajib diisi.',
            'jam_masuk.regex' => 'Format jam masuk tidak valid. Gunakan format HH:mm atau HH:mm:ss.',
            'presensi_start_time.required' => 'Waktu mulai presensi wajib diisi.',
            'presensi_start_time.regex' => 'Format waktu mulai presensi tidak valid. Gunakan format HH:mm atau HH:mm:ss.',
            'grace_period_minutes.required' => 'Grace period wajib diisi.',
            'grace_period_minutes.integer' => 'Grace period harus berupa angka bulat.',
            'grace_period_minutes.min' => 'Grace period minimal 0 menit.',
            'grace_period_minutes.max' => 'Grace period maksimal 60 menit.',
            'cutoff_time.required' => 'Cutoff time wajib diisi.',
            'cutoff_time.regex' => 'Format cutoff time tidak valid. Gunakan format HH:mm atau HH:mm:ss.',
        ]);
        
        // Add custom validation for time comparison
        $validator->after(function ($validator) use ($request) {
            // Ensure all times are in the same format for comparison (HH:mm:ss)
            $jamMasuk = strlen($request->jam_masuk) === 5 ? $request->jam_masuk . ':00' : $request->jam_masuk;
            $cutoffTime = strlen($request->cutoff_time) === 5 ? $request->cutoff_time . ':00' : $request->cutoff_time;
            $presensiStartTime = strlen($request->presensi_start_time) === 5 ? $request->presensi_start_time . ':00' : $request->presensi_start_time;
            
            // Convert to DateTime for comparison
            try {
                $masukTime = new \DateTime($jamMasuk);
                $cutoffTimeObj = new \DateTime($cutoffTime);
                $startTimeObj = new \DateTime($presensiStartTime);
                
                if ($cutoffTimeObj <= $masukTime) {
                    $validator->errors()->add('cutoff_time', 'Cutoff time harus setelah jam masuk.');
                }
                
                if ($startTimeObj >= $masukTime) {
                    $validator->errors()->add('presensi_start_time', 'Waktu mulai presensi harus sebelum jam masuk.');
                }
            } catch (\Exception $e) {
                // If there's an error parsing times, add validation error
                $validator->errors()->add('cutoff_time', 'Format waktu tidak valid.');
            }
        });
        
        if ($validator->fails()) {
            // Log validation errors
            Log::error('System settings validation failed', ['errors' => $validator->errors()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Get or create the settings record
        $settings = SystemSetting::first();
        
        if (!$settings) {
            $settings = new SystemSetting();
        }
        
        // Update the settings
        $settings->location_latitude = $request->location_latitude;
        $settings->location_longitude = $request->location_longitude;
        $settings->location_radius = $request->location_radius;
        $settings->disable_location_validation = $request->disable_location_validation ?? false;
        $settings->grace_period_minutes = $request->grace_period_minutes;
        
        // Handle time formatting - ensure we store in HH:mm:ss format
        $settings->jam_masuk = strlen($request->jam_masuk) === 5 ? $request->jam_masuk . ':00' : $request->jam_masuk;
        $settings->presensi_start_time = strlen($request->presensi_start_time) === 5 ? $request->presensi_start_time . ':00' : $request->presensi_start_time;
        $settings->cutoff_time = strlen($request->cutoff_time) === 5 ? $request->cutoff_time . ':00' : $request->cutoff_time;
        
        $settings->save();
        
        Log::info('System settings updated successfully', ['data' => $settings]);
        
        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan.',
            'data' => $settings
        ]);
    }
    
    // New endpoint to toggle location validation
    public function toggleLocationValidation(Request $request)
    {
        $settings = SystemSetting::first();
        
        if (!$settings) {
            $settings = SystemSetting::create([
                'location_latitude' => 0.52400050,
                'location_longitude' => 123.06047523,
                'location_radius' => 100,
                'disable_location_validation' => false,
                'jam_masuk' => '08:00:00',
                'grace_period_minutes' => 10,
                'cutoff_time' => '18:00:00',
            ]);
        }
        
        // Toggle the disable_location_validation field
        $settings->disable_location_validation = !$settings->disable_location_validation;
        $settings->save();
        
        return response()->json([
            'success' => true,
            'message' => $settings->disable_location_validation 
                ? 'Validasi lokasi telah dinonaktifkan.' 
                : 'Validasi lokasi telah diaktifkan.',
            'data' => $settings
        ]);
    }

    // New endpoint for user testing mode (doesn't affect the actual setting)
    public function toggleTestMode(Request $request)
    {
        // Toggle a session variable for testing mode
        $currentMode = $request->session()->get('testing_mode_disabled', false);
        $newMode = !$currentMode;
        
        $request->session()->put('testing_mode_disabled', $newMode);
        
        return response()->json([
            'success' => true,
            'message' => $newMode 
                ? 'Mode testing telah diaktifkan.' 
                : 'Mode testing telah dinonaktifkan.',
            'testing_mode_disabled' => $newMode
        ]);
    }
}