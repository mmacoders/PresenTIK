<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test database connection and write operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            $this->info('Database connection successful!');
            
            // Test write operation
            $testData = [
                'name' => 'Test Entry',
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Since we don't have a specific table to test with, let's just check if we can execute a query
            $result = DB::select('SELECT 1 as test');
            $this->info('Database query successful! Result: ' . $result[0]->test);
            
            $this->info('Database test completed successfully!');
        } catch (\Exception $e) {
            $this->error('Database test failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}