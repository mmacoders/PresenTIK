<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin
        User::firstOrCreate(
            ['email' => 'superadmin@polda.go.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'jabatan' => 'Super Administrator',
                'status' => 'active',
            ]
        );
        
        // Create admin
        User::firstOrCreate(
            ['email' => 'admin@polda.go.id'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'jabatan' => 'Administrator',
                'status' => 'active',
            ]
        );
        
        // Create pegawai
        User::firstOrCreate(
            ['email' => 'pegawai@polda.go.id'],
            [
                'name' => 'Pegawai',
                'password' => Hash::make('password'),
                'role' => 'user',
                'jabatan' => 'Staff',
                'status' => 'active',
            ]
        );
    }
}