<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::user();
        
        // Get leave requests for all users (since we're removing bidang filter)
        $izinQuery = Izin::with('user');
        
        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $izinQuery->where('tanggal_mulai', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $izinQuery->where('tanggal_selesai', '<=', $request->end_date);
        }
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $izinQuery->whereHas('user', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            });
        }
        
        $izins = $izinQuery->orderBy('created_at', 'desc')->paginate(10);
        
        // Get all active users for the create form
        $users = User::where('status', 'active')
                    ->orderBy('name')
                    ->get();
        
        return Inertia::render('Admin/Izin', [
            'izins' => $izins,
            'filters' => $request->only(['start_date', 'end_date', 'search']),
            'users' => $users,
        ]);
    }
    
    public function create()
    {
        // Get all active users
        $users = User::where('status', 'active')
                    ->orderBy('name')
                    ->get();
        
        return Inertia::render('Admin/IzinCreate', [
            'users' => $users,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_izin' => 'required|in:penuh,parsial',
            'keterangan' => 'required|string|max:500',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // Add file validation
        ]);
        
        // Check if the admin can create izin for this user (admin can create for any user)
        $user = User::find($request->user_id);
        
        // Handle file upload
        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('izin_files', $fileName, 'public');
        }
        
        Izin::create([
            'user_id' => $request->user_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jenis_izin' => $request->jenis_izin,
            'keterangan' => $request->keterangan,
            'file_path' => $filePath, // Save file path
            'status' => 'approved', // Admin can directly approve
            'disetujui_oleh' => Auth::user()->name,
        ]);
        
        return redirect()->route('admin.izin')->with('success', 'Permintaan izin berhasil dibuat dan disetujui.');
    }
    
    public function show(Izin $izin)
    {
        // Admin can view any izin
        $izin->load('user');
        
        return Inertia::render('Admin/IzinShow', [
            'izin' => $izin,
        ]);
    }
    
    public function edit(Izin $izin)
    {
        // Admin can edit any izin
        // Get all active users
        $users = User::where('status', 'active')
                    ->orderBy('name')
                    ->get();
        
        $izin->load('user');
        
        return Inertia::render('Admin/IzinEdit', [
            'izin' => $izin,
            'users' => $users,
        ]);
    }
    
    public function update(Request $request, Izin $izin)
    {
        // For status updates
        if ($request->has('status')) {
            $request->validate([
                'status' => 'required|in:pending,approved,rejected',
                'catatan' => 'nullable|string|max:500',
            ]);
            
            // Admin can update any izin
            $izin->update([
                'status' => $request->status,
                'disetujui_oleh' => Auth::user()->name,
                'catatan' => $request->catatan,
            ]);
            
            return redirect()->back()->with('success', 'Status permintaan izin berhasil diperbarui.');
        }
        
        // For general updates
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_izin' => 'required|in:penuh,parsial',
            'keterangan' => 'required|string|max:500',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // Add file validation
        ]);
        
        // Handle file upload
        $filePath = $izin->file_path; // Keep existing file path
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($izin->file_path) {
                Storage::disk('public')->delete($izin->file_path);
            }
            
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('izin_files', $fileName, 'public');
        }
        
        // Admin can update any izin
        $izin->update([
            'user_id' => $request->user_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jenis_izin' => $request->jenis_izin,
            'keterangan' => $request->keterangan,
            'file_path' => $filePath, // Update file path
        ]);
        
        return redirect()->route('admin.izin')->with('success', 'Permintaan izin berhasil diperbarui.');
    }
    
    public function destroy(Izin $izin)
    {
        // Admin can delete any izin
        // Delete file if exists
        if ($izin->file_path) {
            Storage::disk('public')->delete($izin->file_path);
        }
        
        $izin->delete();
        
        return redirect()->route('admin.izin')->with('success', 'Permintaan izin berhasil dihapus.');
    }
    
    // Method to download izin file
    public function downloadFile(Izin $izin)
    {
        if (!$izin->file_path) {
            abort(404, 'File tidak ditemukan.');
        }
        
        return response()->download(Storage::disk('public')->path($izin->file_path));
    }
}