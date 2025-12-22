<?php
/**
 * Simple Installer Diagnostic
 * Check this first if installer.php gives error 500
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Laravel Installation Diagnostic</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;} .ok{color:green;} .error{color:red;} .box{background:#f5f5f5;padding:15px;margin:10px 0;border-radius:5px;}</style>";

echo "<div class='box'>";
echo "<h2>1. Checking File Structure</h2>";

// Check current directory
echo "<p><strong>Current directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Parent directory:</strong> " . dirname(__DIR__) . "</p>";

// Check vendor
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "<p class='ok'>✅ vendor/autoload.php found</p>";
} else {
    echo "<p class='error'>❌ vendor/autoload.php NOT found</p>";
    echo "<p>Expected path: " . __DIR__ . '/../vendor/autoload.php' . "</p>";
}

// Check bootstrap
if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
    echo "<p class='ok'>✅ bootstrap/app.php found</p>";
} else {
    echo "<p class='error'>❌ bootstrap/app.php NOT found</p>";
    echo "<p>Expected path: " . __DIR__ . '/../bootstrap/app.php' . "</p>";
}

// Check .env
if (file_exists(__DIR__ . '/../.env')) {
    echo "<p class='ok'>✅ .env file found</p>";
} else {
    echo "<p class='error'>❌ .env file NOT found</p>";
}

echo "</div>";

// Try to load Laravel
echo "<div class='box'>";
echo "<h2>2. Loading Laravel</h2>";

try {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require __DIR__ . '/../vendor/autoload.php';
        echo "<p class='ok'>✅ Autoload loaded successfully</p>";
        
        if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
            $app = require_once __DIR__ . '/../bootstrap/app.php';
            echo "<p class='ok'>✅ Laravel application bootstrapped</p>";
            
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            echo "<p class='ok'>✅ Console Kernel created</p>";
            
            echo "<p style='color:green;font-weight:bold;'>🎉 Laravel is working! You can use installer.php</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<details><summary>Stack Trace</summary><pre>" . $e->getTraceAsString() . "</pre></details>";
}

echo "</div>";

// Check PHP version
echo "<div class='box'>";
echo "<h2>3. PHP Environment</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

$required_extensions = ['pdo', 'mbstring', 'openssl', 'json', 'curl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='ok'>✅ Extension: $ext</p>";
    } else {
        echo "<p class='error'>❌ Extension: $ext (missing)</p>";
    }
}
echo "</div>";

// Directory listing
echo "<div class='box'>";
echo "<h2>4. Directory Contents</h2>";
echo "<p><strong>Files in " . dirname(__DIR__) . ":</strong></p>";
echo "<ul>";
$files = scandir(dirname(__DIR__));
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $isDir = is_dir(dirname(__DIR__) . '/' . $file);
        echo "<li>" . ($isDir ? "📁" : "📄") . " $file</li>";
    }
}
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If all checks passed (green), try <a href='installer.php'>installer.php</a></li>";
echo "<li>If vendor missing: run 'composer install' locally first</li>";
echo "<li>If .env missing: copy .env.example to .env and configure</li>";
echo "<li>If still error: check Laravel logs in storage/logs/</li>";
echo "</ol>";
?>
