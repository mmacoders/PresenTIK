<?php

namespace Database\Seeders;

use App\Models\Izin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IzinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some sample users
        $users = User::where('role', 'user')->take(5)->get();
        
        foreach ($users as $index => $user) {
            // Check if an izin already exists for this user and date
            $existingIzin = Izin::where('user_id', $user->id)
                ->where('tanggal_mulai', date('Y-m-d'))
                ->first();
                
            if (!$existingIzin) {
                $tanggal = date('Y-m-d');
                // Create full leave for some users
                if ($index % 2 == 0) {
                    Izin::create([
                        'user_id' => $user->id,
                        'tanggal' => $tanggal,
                        'tanggal_mulai' => $tanggal,
                        'tanggal_selesai' => $tanggal,
                        'jenis_izin' => 'penuh',
                        'keterangan' => 'Sakit demam',
                        'disetujui_oleh' => null,
                        'status' => 'pending',
                        'catatan' => null,
                    ]);
                } else {
                    // Create partial leave for other users
                    Izin::create([
                        'user_id' => $user->id,
                        'tanggal' => $tanggal,
                        'tanggal_mulai' => $tanggal,
                        'tanggal_selesai' => $tanggal,
                        'jenis_izin' => 'parsial',
                        'keterangan' => 'Mengurus dokumen penting',
                        'disetujui_oleh' => null,
                        'status' => 'approved',
                        'catatan' => 'Dokumen sudah diverifikasi',
                    ]);
                }
            }
        }
    }
}