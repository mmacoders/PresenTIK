<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-test 
        {--role=user : The role of the user (user, admin, superadmin)} 
        {--count=1 : Number of users to create}
        {--name= : Name of the user}
        {--nip= : NIP of the user}
        {--nrp= : NRP of the user}
        {--pangkat= : Pangkat of the user}
        {--jabatan= : Jabatan of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test users for development and testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = $this->option('role');
        $count = (int) $this->option('count');
        $name = $this->option('name');
        $nip = $this->option('nip');
        $nrp = $this->option('nrp');
        $pangkat = $this->option('pangkat');
        $jabatan = $this->option('jabatan');
        
        // Validate role
        if (!in_array($role, ['user', 'admin', 'superadmin'])) {
            $this->error('Invalid role. Please use: user, admin, or superadmin');
            return 1;
        }
        
        for ($i = 1; $i <= $count; $i++) {
            // Generate unique email
            $email = "test{$role}{$i}@polda.go.id";
            
            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $this->info("User {$email} already exists, skipping...");
                continue;
            }
            
            // Create user with all available fields
            $userData = [
                'name' => $name ?: "Test " . ucfirst($role) . " {$i}",
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => $role,
                'status' => 'active',
            ];
            
            // Add optional fields if provided
            if ($pangkat) {
                $userData['pangkat'] = $pangkat;
            }
            
            if ($nrp) {
                $userData['nrp'] = $nrp;
            }
            
            if ($nip) {
                $userData['nip'] = $nip;
            }
            
            if ($jabatan) {
                $userData['jabatan'] = $jabatan;
            }
            
            // Create user
            $user = User::create($userData);
            
            $this->info("Created test {$role}: {$user->email} with password: password");
            
            // Show additional fields if provided
            if ($nip || $nrp || $pangkat || $jabatan) {
                $info = "Additional info:";
                if ($pangkat) $info .= " Pangkat={$pangkat}";
                if ($nrp) $info .= " NRP={$nrp}";
                if ($nip) $info .= " NIP={$nip}";
                if ($jabatan) $info .= " Jabatan={$jabatan}";
                $this->info($info);
            }
        }
        
        $this->info("Successfully created {$count} test {$role}(s)!");
        return 0;
    }
}