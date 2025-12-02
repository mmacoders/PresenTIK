<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelolaUserController extends Controller
{
    public function index(Request $request)
    {
        // --- Logic for Pegawai (Users) ---
        $usersQuery = User::where('role', 'user');
        
        // Apply search filter for Users
        if ($request->search && $request->tab !== 'admin') {
            $search = $request->search;
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }
        
        // Paginate Users
        $users = $usersQuery->orderBy('name')->paginate(10, ['*'], 'users_page')->withQueryString();


        // --- Logic for Admins ---
        $adminsQuery = User::where('role', 'admin');

        // Apply search filter for Admins
        if ($request->search && $request->tab === 'admin') {
            $search = $request->search;
            $adminsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        // Paginate Admins
        $admins = $adminsQuery->orderBy('name')->paginate(10, ['*'], 'admins_page')->withQueryString();
        
        return Inertia::render('SuperAdmin/KelolaUser', [
            'users' => $users,
            'admins' => $admins,
            'filters' => $request->only(['search', 'tab']),
        ]);
    }
}
