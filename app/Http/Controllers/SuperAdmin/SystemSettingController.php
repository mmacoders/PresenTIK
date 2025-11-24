<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
                'jam_pulang' => '16:00:00',
            ]);
        }
        
        // Format settings for the frontend
        $formattedSettings = [
            'location_latitude' => $settings->location_latitude,
            'location_longitude' => $settings->location_longitude,
            'location_radius' => $settings->location_radius,
            'disable_location_validation' => $settings->disable_location_validation,
            'work_start_time' => $settings->jam_masuk,
            'work_end_time' => $settings->jam_pulang,
        ];
        
        return Inertia::render('SuperAdmin/NewSystemSettings', [
            'settings' => $formattedSettings,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:settings,key',
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);
        
        // Create new setting
        $setting = SystemSetting::create([
            'key' => $request->name,
            'value' => $request->value,
            'description' => $request->description,
        ]);
        
        return redirect()->back()->with('success', 'Pengaturan berhasil ditambahkan.');
    }
    
    public function update(Request $request)
    {
        // Validate the request for bulk update
        $request->validate([
            'location_latitude' => 'required|numeric|between:-90,90',
            'location_longitude' => 'required|numeric|between:-180,180',
            'location_radius' => 'required|integer|min:10|max:1000',
            'disable_location_validation' => 'boolean',
            'work_start_time' => 'required|string|regex:/^\d{2}:\d{2}(:\d{2})?$/',
            'work_end_time' => 'required|string|regex:/^\d{2}:\d{2}(:\d{2})?$/',
        ]);
        
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
        
        // Handle time formatting - ensure we store in HH:mm:ss format
        $settings->jam_masuk = strlen($request->work_start_time) === 5 ? $request->work_start_time . ':00' : $request->work_start_time;
        $settings->jam_pulang = strlen($request->work_end_time) === 5 ? $request->work_end_time . ':00' : $request->work_end_time;
        
        $settings->save();
        
        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        // We don't allow deletion of the system settings record
        return redirect()->back()->with('error', 'Operasi tidak didukung.');
    }
}