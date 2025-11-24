<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        // Build query for users (same as SuperAdmin)
        $usersQuery = User::where('role', 'user');
        
        // Apply search filter
        if ($request->search) {
            $search = $request->search;
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->status) {
            $usersQuery->where('status', $request->status);
        }
        
        // Paginate results
        $users = $usersQuery->orderBy('name')->paginate(12)->withQueryString();
        
        return Inertia::render('Admin/Pegawai', [
            'users' => $users,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
    
    public function show(User $user)
    {
        // Ensure user is a pegawai (user role)
        if ($user->role !== 'user') {
            abort(404);
        }
        
        // Get attendance statistics for current month
        $currentMonth = now()->format('Y-m');
        $attendances = $user->absensis()
            ->where('tanggal', 'like', "{$currentMonth}%")
            ->get();
        
        // Calculate statistics
        $stats = [
            'hadir' => 0,
            'izin' => 0,
            'terlambat' => 0,
        ];
        
        foreach ($attendances as $attendance) {
            switch ($attendance->status) {
                case 'hadir':
                case 'Hadir':
                    $stats['hadir']++;
                    break;
                case 'izin':
                case 'Izin':
                case 'Izin (Valid)':
                    $stats['izin']++;
                    break;
                case 'terlambat':
                case 'Terlambat':
                    $stats['terlambat']++;
                    break;
            }
        }
        
        return Inertia::render('Admin/PegawaiDetail', [
            'pegawai' => $user,
            'stats' => $stats,
            'attendances' => $attendances,
        ]);
    }
    
    public function toggleStatus(User $user)
    {
        // Ensure user is a pegawai (user role)
        if ($user->role !== 'user') {
            abort(404);
        }
        
        $user->update([
            'status' => $user->status === 'aktif' ? 'nonaktif' : 'aktif',
        ]);
        
        return redirect()->back()->with('success', 'Status pegawai berhasil diubah.');
    }
}