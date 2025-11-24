<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for system settings
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/system-settings', [App\Http\Controllers\API\SystemSettingController::class, 'index'])->name('api.system-settings.index');
    Route::put('/system-settings', [App\Http\Controllers\API\SystemSettingController::class, 'update'])->name('api.system-settings.update');
    Route::post('/system-settings/toggle-location-validation', [App\Http\Controllers\API\SystemSettingController::class, 'toggleLocationValidation'])->name('api.system-settings.toggle-location-validation');
});

// User API routes
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::get('/user/attendance/today', [App\Http\Controllers\User\AbsensiController::class, 'getTodayAttendance']);
    Route::post('/user/attendance/check-in', [App\Http\Controllers\User\AbsensiController::class, 'checkIn']);
    Route::post('/user/attendance/check-out', [App\Http\Controllers\User\AbsensiController::class, 'checkOut']);
});

// Admin API routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/attendance/report', [App\Http\Controllers\Admin\LaporanController::class, 'getReportData']);
});

// SuperAdmin API routes
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/dashboard/stats', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'getStats']);
});