<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Add profile picture URL if exists
        if ($user->profile_pict) {
            $user->profile_pict_url = Storage::url($user->profile_pict);
        }
        
        return Inertia::render('User/UpdateProfil', [
            'user' => $user,
        ]);
    }
    
    public function update(Request $request)
    {
        $user = auth()->user();
        
        // Validate the request
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:255'],
            'nrp' => ['nullable', 'string', 'max:255'],
            'pangkat' => ['nullable', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'profile_pict' => ['nullable', 'image', 'max:2048'], // Max 2MB
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        
        // Handle profile picture upload
        if ($request->hasFile('profile_pict')) {
            // Delete old profile picture if exists
            if ($user->profile_pict) {
                Storage::delete($user->profile_pict);
            }
            
            // Store new profile picture
            $path = $request->file('profile_pict')->store('profile_pictures', 'public');
            $user->profile_pict = $path;
        }
        
        // Update user data
        // Only update password if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            unset($validated['password']); // Remove password from the update data
        }
        
        // Only update fields that were actually sent in the request
        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
            
            // Reset email verification if email changed
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
        }
        
        // Update other fields that were sent
        $fillableFields = ['nip', 'nrp', 'pangkat', 'jabatan', 'no_hp'];
        foreach ($fillableFields as $field) {
            if (array_key_exists($field, $validated)) {
                $user->$field = $validated[$field];
            }
        }
        
        $user->save();
        
        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
    
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }
        
        // Update password
        $user->update(['password' => Hash::make($request->password)]);
        
        return redirect()->back()->with('success', 'Password berhasil diperbarui.');
    }
}