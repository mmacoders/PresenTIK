<?php

/**
 * Laravel Application Installer
 * 
 * This file allows you to run essential Laravel commands via web browser
 * when you don't have CLI access on your hosting.
 * 
 * IMPORTANT: Delete this file after installation for security!
 * 
 * Usage: 
 * 1. Upload semua file ke hosting
 * 2. Akses: http://yourdomain.com/installer.php
 * 3. Jalankan commands yang diperlukan
 * 4. HAPUS file ini setelah selesai!
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent direct access after installation
if (file_exists(__DIR__ . '/.installation_completed')) {
    die('Installation already completed. Please delete this file for security.');
}

// Check if vendor autoload exists
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('Error: vendor/autoload.php not found. Please run "composer install" first or check your file structure.');
}

// Check if bootstrap/app.php exists  
if (!file_exists(__DIR__ . '/../bootstrap/app.php')) {
    die('Error: bootstrap/app.php not found. Please check your Laravel installation path.');
}

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
} catch (Exception $e) {
    die('Error loading Laravel: ' . $e->getMessage() . '<br><br>Stack trace:<br>' . nl2br($e->getTraceAsString()));
}

// Get command from query parameter
$command = $_GET['command'] ?? '';
$confirm = $_GET['confirm'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PresenTIK - Laravel Installer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        .command-list {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        .command-item {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s;
        }
        .command-item:hover {
            border-color: #dc2626;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
        }
        .command-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }
        .command-desc {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
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
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .output-box {
            background: #1f2937;
            color: #10b981;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            overflow-x: auto;
            margin-top: 20px;
            white-space: pre-wrap;
        }
        .steps {
            counter-reset: step;
            list-style: none;
            padding-left: 0;
        }
        .steps li {
            counter-increment: step;
            padding: 15px 15px 15px 50px;
            position: relative;
            margin-bottom: 10px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .steps li::before {
            content: counter(step);
            position: absolute;
            left: 15px;
            top: 15px;
            background: #dc2626;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }
        .footer {
            background: #f9fafb;
            padding: 20px 30px;
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
            <h1>🚀 PresenTIK Installer</h1>
            <p>Laravel Application Setup Without CLI Access</p>
        </div>

        <div class="content">
            <?php if (!$command): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ PENTING!</strong><br>
                    File ini hanya untuk instalasi awal. <strong>HAPUS file installer.php setelah selesai</strong> untuk keamanan!
                </div>

                <div class="alert alert-info">
                    <strong>📋 Langkah Instalasi:</strong>
                    <ol class="steps" style="margin-top: 15px;">
                        <li>Pastikan file <code>.env</code> sudah dikonfigurasi dengan database yang benar</li>
                        <li>Jalankan "Migrate Database" untuk membuat tabel database</li>
                        <li>Jalankan "Optimize Application" untuk cache config</li>
                        <li>Jalankan "Create Storage Link" untuk symlink storage</li>
                        <li>Akses aplikasi dan login</li>
                        <li><strong>HAPUS file installer.php ini!</strong></li>
                    </ol>
                </div>

                <h3 style="margin-bottom: 20px; color: #111827;">Available Commands:</h3>

                <div class="command-list">
                    <div class="command-item">
                        <div class="command-title">🗄️ Migrate Database</div>
                        <div class="command-desc">Membuat semua tabel database yang diperlukan aplikasi</div>
                        <a href="?command=migrate" class="btn btn-primary">Run Migration</a>
                    </div>

                    <div class="command-item">
                        <div class="command-title">🔗 Create Storage Link</div>
                        <div class="command-desc">Membuat symbolic link untuk folder storage agar file upload bisa diakses</div>
                        <a href="?command=storage-link" class="btn btn-primary">Create Link</a>
                    </div>

                    <div class="command-item">
                        <div class="command-title">⚡ Optimize Application</div>
                        <div class="command-desc">Cache konfigurasi, routes, dan views untuk performa maksimal</div>
                        <a href="?command=optimize" class="btn btn-primary">Optimize</a>
                    </div>

                    <div class="command-item">
                        <div class="command-title">🧹 Clear All Cache</div>
                        <div class="command-desc">Hapus semua cache (config, routes, views, compiled)</div>
                        <a href="?command=clear-cache" class="btn btn-primary">Clear Cache</a>
                    </div>

                    <div class="command-item">
                        <div class="command-title">✅ Mark Installation Complete</div>
                        <div class="command-desc">Tandai instalasi selesai dan nonaktifkan installer ini</div>
                        <a href="?command=complete" class="btn btn-success">Complete Installation</a>
                    </div>
                </div>

            <?php elseif ($command === 'migrate'): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Perhatian!</strong><br>
                    Command ini akan membuat/mengubah tabel database. Pastikan backup database jika sudah ada data!
                </div>
                
                <?php if ($confirm !== 'yes'): ?>
                    <p style="margin: 20px 0;">Apakah Anda yakin ingin menjalankan migration?</p>
                    <a href="?command=migrate&confirm=yes" class="btn btn-danger">Ya, Jalankan Migration</a>
                    <a href="?" class="btn btn-primary" style="background: #6b7280;">Batal</a>
                <?php else: ?>
                    <?php
                    ob_start();
                    $status = $kernel->call('migrate', ['--force' => true]);
                    $output = ob_get_clean();
                    ?>
                    <div class="alert alert-success">
                        <strong>✅ Migration Berhasil!</strong>
                    </div>
                    <div class="output-box"><?php echo htmlspecialchars($output); ?></div>
                    <a href="?" class="btn btn-primary" style="margin-top: 20px;">Kembali</a>
                <?php endif; ?>

            <?php elseif ($command === 'storage-link'): ?>
                <?php
                ob_start();
                $status = $kernel->call('storage:link');
                $output = ob_get_clean();
                ?>
                <div class="alert alert-success">
                    <strong>✅ Storage Link Berhasil Dibuat!</strong>
                </div>
                <div class="output-box"><?php echo htmlspecialchars($output); ?></div>
                <a href="?" class="btn btn-primary" style="margin-top: 20px;">Kembali</a>

            <?php elseif ($command === 'optimize'): ?>
                <?php
                ob_start();
                $kernel->call('config:cache');
                $kernel->call('route:cache');
                $kernel->call('view:cache');
                $output = ob_get_clean();
                ?>
                <div class="alert alert-success">
                    <strong>✅ Optimization Berhasil!</strong><br>
                    Config, routes, dan views sudah di-cache.
                </div>
                <div class="output-box"><?php echo htmlspecialchars($output); ?></div>
                <a href="?" class="btn btn-primary" style="margin-top: 20px;">Kembali</a>

            <?php elseif ($command === 'clear-cache'): ?>
                <?php
                ob_start();
                $kernel->call('optimize:clear');
                $output = ob_get_clean();
                ?>
                <div class="alert alert-success">
                    <strong>✅ Cache Berhasil Dihapus!</strong>
                </div>
                <div class="output-box"><?php echo htmlspecialchars($output); ?></div>
                <a href="?" class="btn btn-primary" style="margin-top: 20px;">Kembali</a>

            <?php elseif ($command === 'complete'): ?>
                <?php
                file_put_contents(__DIR__ . '/.installation_completed', date('Y-m-d H:i:s'));
                ?>
                <div class="alert alert-success">
                    <strong>🎉 Instalasi Selesai!</strong><br><br>
                    <strong>LANGKAH TERAKHIR:</strong><br>
                    1. <strong>HAPUS file installer.php dari server Anda SEKARANG!</strong><br>
                    2. Akses aplikasi di URL utama Anda<br>
                    3. Login dengan credentials default (jika ada)
                </div>
                <div style="margin-top: 20px; padding: 20px; background: #fee2e2; border-radius: 8px; color: #991b1b;">
                    <strong>⚠️ PENTING - HAPUS FILE INI!</strong><br>
                    File installer.php adalah security risk jika dibiarkan. Hapus sekarang juga!
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <strong>PresenTIK</strong> - Sistem Presensi POLDA TIK<br>
            Powered by Laravel & Inertia.js
        </div>
    </div>
</body>
</html>
