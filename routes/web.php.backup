<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\PegawaiController as AdminPegawaiController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\LaporanController as SuperAdminLaporanController;
use App\Http\Controllers\SuperAdmin\PegawaiController as SuperAdminPegawaiController;
use App\Http\Controllers\SuperAdmin\ProfilController as SuperAdminProfilController;
use App\Http\Controllers\SuperAdmin\SystemSettingController as SuperAdminSystemSettingController;
use App\Http\Controllers\SuperAdmin\AdminController as SuperAdminAdminController;
use App\Http\Controllers\User\AbsensiController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfilController as UserProfilController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// User routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/user/absensi', [AbsensiController::class, 'index'])->name('user.absensi');
    Route::post('/user/absensi/check-in', [AbsensiController::class, 'checkIn'])->name('user.absensi.checkin');
    Route::post('/user/absensi/check-out', [AbsensiController::class, 'checkOut'])->name('user.absensi.checkout');
    Route::post('/user/absensi/permission', [AbsensiController::class, 'requestPermission'])->name('user.absensi.permission');
    Route::get('/user/profil', [UserProfilController::class, 'index'])->name('user.profil');
    Route::match(['patch', 'post'], '/user/profil', [UserProfilController::class, 'update'])->name('user.profil.update');
    Route::patch('/user/profil/password', [UserProfilController::class, 'updatePassword'])->name('user.profil.password');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/pegawai', [AdminPegawaiController::class, 'index'])->name('admin.pegawai');
    Route::get('/admin/pegawai/{user}', [AdminPegawaiController::class, 'show'])->name('admin.pegawai.show');
    Route::patch('/admin/pegawai/{user}/toggle-status', [AdminPegawaiController::class, 'toggleStatus'])->name('admin.pegawai.toggle-status');
    Route::get('/admin/laporan', [AdminLaporanController::class, 'index'])->name('admin.laporan');
    Route::post('/admin/laporan/export', [AdminLaporanController::class, 'export'])->name('admin.laporan.export');
    // Izin & Cuti routes
    Route::get('/admin/izin', [App\Http\Controllers\Admin\IzinController::class, 'index'])->name('admin.izin');
    Route::get('/admin/izin/create', [App\Http\Controllers\Admin\IzinController::class, 'create'])->name('admin.izin.create');
    Route::post('/admin/izin', [App\Http\Controllers\Admin\IzinController::class, 'store'])->name('admin.izin.store');
    Route::get('/admin/izin/{izin}', [App\Http\Controllers\Admin\IzinController::class, 'show'])->name('admin.izin.show');
    Route::get('/admin/izin/{izin}/edit', [App\Http\Controllers\Admin\IzinController::class, 'edit'])->name('admin.izin.edit');
    Route::patch('/admin/izin/{izin}', [App\Http\Controllers\Admin\IzinController::class, 'update'])->name('admin.izin.update');
    Route::delete('/admin/izin/{izin}', [App\Http\Controllers\Admin\IzinController::class, 'destroy'])->name('admin.izin.destroy');
    Route::get('/admin/izin/{izin}/download', [App\Http\Controllers\Admin\IzinController::class, 'downloadFile'])->name('admin.izin.download');
    // Route for marking absent users
    Route::post('/admin/absensi/mark-absent', [AbsensiController::class, 'markAbsentUsers'])->name('admin.absensi.mark-absent');
    // Profil routes
    Route::get('/admin/profil', [AdminProfilController::class, 'index'])->name('admin.profil');
    Route::match(['patch', 'post'], '/admin/profil', [AdminProfilController::class, 'update'])->name('admin.profil.update');
});

// SuperAdmin routes
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/dashboard', [SuperAdminDashboardController::class, 'index'])->name('superadmin.dashboard');
    Route::get('/superadmin/admin', [SuperAdminAdminController::class, 'index'])->name('superadmin.admin');
    Route::post('/superadmin/admin', [SuperAdminAdminController::class, 'store'])->name('superadmin.admin.store');
    Route::patch('/superadmin/admin/{user}', [SuperAdminAdminController::class, 'update'])->name('superadmin.admin.update');
    Route::delete('/superadmin/admin/{user}', [SuperAdminAdminController::class, 'destroy'])->name('superadmin.admin.destroy');
    Route::patch('/superadmin/admin/{user}/toggle-status', [SuperAdminAdminController::class, 'toggleStatus'])->name('superadmin.admin.toggle-status');
    Route::patch('/superadmin/admin/{user}/transfer', [SuperAdminAdminController::class, 'transfer'])->name('superadmin.admin.transfer');
    
    // Pegawai Management
    Route::get('/superadmin/pegawai', [SuperAdminPegawaiController::class, 'index'])->name('superadmin.pegawai');
    Route::post('/superadmin/pegawai', [SuperAdminPegawaiController::class, 'store'])->name('superadmin.pegawai.store');
    Route::get('/superadmin/pegawai/{user}', [SuperAdminPegawaiController::class, 'show'])->name('superadmin.pegawai.show');
    Route::patch('/superadmin/pegawai/{user}', [SuperAdminPegawaiController::class, 'update'])->name('superadmin.pegawai.update');
    Route::delete('/superadmin/pegawai/{user}', [SuperAdminPegawaiController::class, 'destroy'])->name('superadmin.pegawai.destroy');
    Route::patch('/superadmin/pegawai/{user}/toggle-status', [SuperAdminPegawaiController::class, 'toggleStatus'])->name('superadmin.pegawai.toggle-status');
    
    // Laporan Global
    Route::get('/superadmin/laporan', [SuperAdminLaporanController::class, 'index'])->name('superadmin.laporan');
    Route::post('/superadmin/laporan/export/excel', [SuperAdminLaporanController::class, 'exportExcel'])->name('superadmin.laporan.export.excel');
    Route::post('/superadmin/laporan/export/pdf', [SuperAdminLaporanController::class, 'exportPDF'])->name('superadmin.laporan.export.pdf');
    
    // System Settings
    Route::get('/superadmin/settings', [SuperAdminSystemSettingController::class, 'index'])->name('superadmin.settings');
    Route::post('/superadmin/settings', [SuperAdminSystemSettingController::class, 'store'])->name('superadmin.settings.store');
    Route::put('/superadmin/settings', [SuperAdminSystemSettingController::class, 'update'])->name('superadmin.settings.update');
    Route::delete('/superadmin/settings/{setting}', [SuperAdminSystemSettingController::class, 'destroy'])->name('superadmin.settings.destroy');
    
    // Route for marking absent users (also available for super admin)
    Route::post('/superadmin/absensi/mark-absent', [AbsensiController::class, 'markAbsentUsers'])->name('superadmin.absensi.mark-absent');
    
    // Profil routes
    Route::get('/superadmin/profil', [SuperAdminProfilController::class, 'index'])->name('superadmin.profil');
    Route::match(['patch', 'post'], '/superadmin/profil', [SuperAdminProfilController::class, 'update'])->name('superadmin.profil.update');
});

// API routes for system settings - accessible by users for testing
Route::middleware(['auth'])->group(function () {
    Route::post('/api/system-settings/toggle-test-mode', [App\Http\Controllers\API\SystemSettingController::class, 'toggleTestMode'])->name('api.system-settings.toggle-test');
});

// API routes for system settings - restricted to superadmin
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/api/system-settings', [App\Http\Controllers\API\SystemSettingController::class, 'index'])->name('api.system-settings.index');
    Route::put('/api/system-settings', [App\Http\Controllers\API\SystemSettingController::class, 'update'])->name('api.system-settings.update');
    Route::post('/api/system-settings/toggle', [App\Http\Controllers\API\SystemSettingController::class, 'toggleLocationValidation'])->name('api.system-settings.toggle');
});

Route::get('/dashboard', function () {
    // Redirect based on user role
    if (auth()->check()) {
        switch (auth()->user()->role) {
            case 'user':
                return redirect()->route('user.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'superadmin':
                return redirect()->route('superadmin.dashboard');
            default:
                return Inertia::render('Dashboard');
        }
    }
    
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';