<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    /**
     * Check if setup is allowed
     */
    private function isSetupAllowed()
    {
        // Only allow if .env is in development or if setup file exists
        if (config('app.env') === 'production' && !file_exists(base_path('.allow_setup'))) {
            abort(403, 'Setup is disabled. Create .allow_setup file in root to enable.');
        }
    }

    /**
     * Show setup page
     */
    public function index()
    {
        $this->isSetupAllowed();

        $status = [
            'database' => $this->checkDatabase(),
            'storage_link' => $this->checkStorageLink(),
            'env' => file_exists(base_path('.env')),
        ];

        return view('setup.index', compact('status'));
    }

    /**
     * Run migrations
     */
    public function migrate(Request $request)
    {
        $this->isSetupAllowed();

        try {
            $output = [];
            
            // Run migrations
            Artisan::call('migrate', [
                '--force' => true,
            ]);
            
            $output[] = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Migration completed successfully!',
                'output' => implode("\n", $output)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
                'output' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Create storage link
     */
    public function storageLink()
    {
        $this->isSetupAllowed();

        try {
            Artisan::call('storage:link');
            
            return response()->json([
                'success' => true,
                'message' => 'Storage link created successfully!',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create storage link: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize application
     */
    public function optimize()
    {
        $this->isSetupAllowed();

        try {
            $output = [];
            
            Artisan::call('config:cache');
            $output[] = "Config cached: " . Artisan::output();
            
            Artisan::call('route:cache');
            $output[] = "Routes cached: " . Artisan::output();
            
            Artisan::call('view:cache');
            $output[] = "Views cached: " . Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully!',
                'output' => implode("\n", $output)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all cache
     */
    public function clearCache()
    {
        $this->isSetupAllowed();

        try {
            Artisan::call('optimize:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully!',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check database connection
     */
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check storage link
     */
    private function checkStorageLink()
    {
        return file_exists(public_path('storage'));
    }

    /**
     * Disable setup
     */
    public function disable()
    {
        $this->isSetupAllowed();

        $setupFile = base_path('.allow_setup');
        if (file_exists($setupFile)) {
            unlink($setupFile);
        }

        return response()->json([
            'success' => true,
            'message' => 'Setup has been disabled. Delete .allow_setup file to re-enable.'
        ]);
    }
}
