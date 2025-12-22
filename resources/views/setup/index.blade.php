<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PresenTIK - Application Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 40px; }
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid;
        }
        .alert-warning { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
        .alert-success { background: #d1fae5; color: #065f46; border-color: #10b981; }
        .alert-danger { background: #fee2e2; color: #991b1b; border-color: #dc2626; }
        .alert-info { background: #dbeafe; color: #1e40af; border-color: #3b82f6; }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .status-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            text-align: center;
        }
        .status-card.success { border-color: #10b981; background: #d1fae5; }
        .status-card.error { border-color: #dc2626; background: #fee2e2; }
        .status-icon { font-size: 32px; margin-bottom: 10px; }
        .status-label { font-size: 14px; color: #6b7280; font-weight: 600; }
        
        .command-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }
        .command-card {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 25px;
            transition: all 0.3s;
        }
        .command-card:hover {
            border-color: #dc2626;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
        }
        .command-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        .command-desc {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #dc2626;
            color: white;
        }
        .btn-primary:hover {
            background: #991b1b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }
        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        
        .output-box {
            background: #1f2937;
            color: #10b981;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            overflow-x: auto;
            margin-top: 15px;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
            display: none;
        }
        .output-box.show { display: block; }
        
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 PresenTIK Setup</h1>
            <p>Application Setup & Migration Tool</p>
        </div>

        <div class="content">
            <div class="alert alert-warning">
                <strong>⚠️ IMPORTANT SECURITY NOTICE</strong><br>
                This setup page should only be accessible during initial installation.
                <strong>Disable this after setup is complete!</strong>
            </div>

            <!-- System Status -->
            <h3 style="margin-bottom: 20px; color: #111827;">📊 System Status</h3>
            <div class="status-grid">
                <div class="status-card {{ $status['database'] ? 'success' : 'error' }}">
                    <div class="status-icon">{{ $status['database'] ? '✅' : '❌' }}</div>
                    <div class="status-label">Database Connection</div>
                </div>
                <div class="status-card {{ $status['storage_link'] ? 'success' : 'error' }}">
                    <div class="status-icon">{{ $status['storage_link'] ? '✅' : '❌' }}</div>
                    <div class="status-label">Storage Link</div>
                </div>
                <div class="status-card {{ $status['env'] ? 'success' : 'error' }}">
                    <div class="status-icon">{{ $status['env'] ? '✅' : '❌' }}</div>
                    <div class="status-label">.env File</div>
                </div>
            </div>

            @if(!$status['database'])
                <div class="alert alert-danger">
                    <strong>❌ Database Connection Failed</strong><br>
                    Please check your .env database configuration before running migration!
                </div>
            @endif

            <!-- Setup Commands -->
            <h3 style="margin-bottom: 20px; color: #111827;">🔧 Setup Commands</h3>
            <div class="command-grid">
                <!-- Migrate -->
                <div class="command-card">
                    <div class="command-title">🗄️ Run Database Migration</div>
                    <div class="command-desc">Create all database tables and schema</div>
                    <button 
                        onclick="runCommand('migrate', this)" 
                        class="btn btn-primary"
                        {{ !$status['database'] ? 'disabled' : '' }}
                    >
                        Run Migration
                    </button>
                    <div class="output-box" id="output-migrate"></div>
                </div>

                <!-- Storage Link -->
                <div class="command-card">
                    <div class="command-title">🔗 Create Storage Link</div>
                    <div class="command-desc">Create symbolic link for file uploads</div>
                    <button onclick="runCommand('storage-link', this)" class="btn btn-primary">
                        Create Link
                    </button>
                    <div class="output-box" id="output-storage-link"></div>
                </div>

                <!-- Optimize -->
                <div class="command-card">
                    <div class="command-title">⚡ Optimize Application</div>
                    <div class="command-desc">Cache config, routes, and views for production</div>
                    <button onclick="runCommand('optimize', this)" class="btn btn-primary">
                        Optimize Now
                    </button>
                    <div class="output-box" id="output-optimize"></div>
                </div>

                <!-- Clear Cache -->
                <div class="command-card">
                    <div class="command-title">🧹 Clear All Cache</div>
                    <div class="command-desc">Clear all cached data (config, routes, views)</div>
                    <button onclick="runCommand('clear-cache', this)" class="btn btn-primary">
                        Clear Cache
                    </button>
                    <div class="output-box" id="output-clear-cache"></div>
                </div>
            </div>

            <!-- Disable Setup -->
            <div class="alert alert-info">
                <strong>✅ Setup Complete?</strong><br>
                After completing all setup steps, disable this setup page for security.
                <br><br>
                <button onclick="disableSetup(this)" class="btn btn-danger">
                    Disable Setup (Recommended)
                </button>
            </div>
        </div>

        <div class="footer">
            <strong>PresenTIK</strong> - Sistem Presensi POLDA TIK<br>
            Powered by Laravel & Inertia.js
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        async function runCommand(command, button) {
            const outputBox = document.getElementById(`output-${command}`);
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<span class="loading"></span> Running...';
            outputBox.classList.add('show');
            outputBox.innerHTML = 'Executing command...\n';

            try {
                const response = await fetch(`/setup/${command}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    }
                });

                const data = await response.json();

                if (data.success) {
                    outputBox.innerHTML = '✅ SUCCESS!\n\n' + data.output;
                    outputBox.style.color = '#10b981';
                    
                    // Show success alert
                    showAlert('success', data.message);
                    
                    // Reload status
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    outputBox.innerHTML = '❌ ERROR!\n\n' + data.message + '\n\n' + (data.output || '');
                    outputBox.style.color = '#ef4444';
                    showAlert('error', data.message);
                }
            } catch (error) {
                outputBox.innerHTML = '❌ NETWORK ERROR!\n\n' + error.message;
                outputBox.style.color = '#ef4444';
                showAlert('error', 'Network error: ' + error.message);
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        async function disableSetup(button) {
            if (!confirm('This will disable the setup page. You will need to manually enable it again. Continue?')) {
                return;
            }

            button.disabled = true;
            button.innerHTML = '<span class="loading"></span> Disabling...';

            try {
                const response = await fetch('/setup/disable', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ Setup has been disabled!\n\nTo re-enable: Create file .allow_setup in your application root.');
                    window.location.href = '/';
                } else {
                    alert('❌ Failed to disable setup: ' + data.message);
                    button.disabled = false;
                    button.innerHTML = 'Disable Setup';
                }
            } catch (error) {
                alert('❌ Error: ' + error.message);
                button.disabled = false;
                button.innerHTML = 'Disable Setup';
            }
        }

        function showAlert(type, message) {
            // Simple alert for now
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    </script>
</body>
</html>
