<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsentUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absensi:mark-absent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark users as absent (alpha) if they miss both check-in and check-out';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Marking absent users...');
        
        // Get system settings for working hours
        $systemSettings = SystemSetting::first();
        $jamPulang = $systemSettings ? $systemSettings->jam_pulang : '17:00:00';
        
        // Get today's date
        $today = Carbon::today()->format('Y-m-d');
        
        // Get all active users
        $users = User::where('status', 'active')->get();
        
        $absentCount = 0;
        
        foreach ($users as $user) {
            // Skip super admin users as they might not be required to attend
            if ($user->role === 'superadmin') {
                continue;
            }
            
            // Check if user already has an attendance record for today
            $attendance = Absensi::where('user_id', $user->id)
                ->where('tanggal', $today)
                ->first();
                
            // If no attendance record exists, mark as absent
            if (!$attendance) {
                Absensi::create([
                    'user_id' => $user->id,
                    'tanggal' => $today,
                    'status' => 'alpha', // Absent
                    'keterangan' => 'Tidak melakukan absensi masuk dan keluar',
                ]);
                
                $absentCount++;
                $this->info("Marked user {$user->name} (ID: {$user->id}) as absent for {$today}");
                continue;
            }
            
            // If attendance record exists but has no check-in or check-out
            if (!$attendance->waktu_masuk && !$attendance->waktu_keluar) {
                // Update status to absent if not already set
                if ($attendance->status !== 'alpha') {
                    $attendance->update([
                        'status' => 'alpha',
                        'keterangan' => 'Tidak melakukan absensi masuk dan keluar',
                    ]);
                    
                    $absentCount++;
                    $this->info("Updated user {$user->name} (ID: {$user->id}) as absent for {$today}");
                }
                continue;
            }
            
            // If user has partial attendance (either check-in or check-out but not both)
            // And it's past the end of workday, mark as absent
            $currentTime = Carbon::now();
            $endOfWorkday = Carbon::today()->setTimeFromTimeString($jamPulang);
            
            if ($currentTime->greaterThan($endOfWorkday)) {
                // If user only checked in but not out
                if ($attendance->waktu_masuk && !$attendance->waktu_keluar) {
                    // Check if this is not a partial leave case
                    $isPartialLeave = $user->izins()
                        ->where('tanggal_mulai', '<=', $today)
                        ->where('tanggal_selesai', '>=', $today)
                        ->where('jenis_izin', 'parsial')
                        ->exists();
                        
                    if (!$isPartialLeave) {
                        $attendance->update([
                            'status' => 'alpha',
                            'keterangan' => 'Tidak melakukan absensi keluar',
                        ]);
                        
                        $absentCount++;
                        $this->info("Marked user {$user->name} (ID: {$user->id}) as absent for not checking out on {$today}");
                    }
                }
                // If user only checked out but not in (unusual case)
                elseif (!$attendance->waktu_masuk && $attendance->waktu_keluar) {
                    $attendance->update([
                        'status' => 'alpha',
                        'keterangan' => 'Tidak melakukan absensi masuk',
                    ]);
                    
                    $absentCount++;
                    $this->info("Marked user {$user->name} (ID: {$user->id}) as absent for not checking in on {$today}");
                }
            }
        }
        
        $this->info("Completed marking absent users. {$absentCount} users marked as absent.");
        return 0;
    }
}