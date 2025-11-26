<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        // Get all admins with pagination
        $admins = User::where('role', 'admin')->paginate(10);
        
        return Inertia::render('SuperAdmin/KelolaAdmin', [
            'admins' => $admins,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nrp' => 'required|string',
        ]);
        
        $user = User::where('nrp', $request->nrp)->first();
        
        if (!$user) {
            return redirect()->back()->withErrors(['nrp' => 'NRP tidak teregistrasi.']);
        }
        
        // Cek jika user sudah admin atau superadmin
        if ($user->role === 'admin' || $user->role === 'superadmin') {
            return redirect()->back()->with('error', 'User dengan NRP tersebut sudah menjadi Admin atau Super Admin.');
        }
        
        $user->update([
            'role' => 'admin'
        ]);
        
        return redirect()->back()->with('success', 'Pegawai berhasil dijadikan Admin.');
    }
    
    public function update(Request $request, $id)
    {
        // Find the admin user
        $admin = User::findOrFail($id);
        
        // Ensure we're updating an admin
        if ($admin->role !== 'admin') {
            abort(403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            'jabatan' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);
        
        // Update admin
        $admin->update($request->only('name', 'email', 'jabatan', 'status'));
        
        return redirect()->back()->with('success', 'Admin berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        // Find the admin user
        $admin = User::findOrFail($id);
        
        // Ensure we're deleting an admin
        if ($admin->role !== 'admin') {
            abort(403);
        }
        
        $admin->delete();
        
        return redirect()->back()->with('success', 'Admin berhasil dihapus.');
    }
    
    public function toggleStatus($id)
    {
        // Find the admin user
        $admin = User::findOrFail($id);
        
        // Ensure we're toggling an admin
        if ($admin->role !== 'admin') {
            abort(403);
        }
        
        // Toggle status
        $admin->update([
            'status' => $admin->status === 'active' ? 'inactive' : 'active'
        ]);
        
        $statusText = $admin->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()->with('success', "Admin berhasil {$statusText}.");
    }
    
    public function transfer(Request $request, $id)
    {
        // Find the admin user
        $admin = User::findOrFail($id);
        
        // Ensure we're updating an admin
        if ($admin->role !== 'admin') {
            abort(403);
        }
        
        $request->validate([
            'jabatan' => 'nullable|string|max:255',
        ]);
        
        // Update jabatan
        $admin->update([
            'jabatan' => $request->jabatan
        ]);
        
        return redirect()->back()->with('success', "Admin berhasil diperbarui jabatannya.");
    }
}