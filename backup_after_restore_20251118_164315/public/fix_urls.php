<?php
// Quick fix for URL issues
echo "<h1>URL Path Diagnostic</h1><pre>";

// Check current paths
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "PHP Self: " . $_SERVER['PHP_SELF'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n\n";

// Check if we're in public folder
$inPublicFolder = (strpos($_SERVER['SCRIPT_FILENAME'], '\public\\') !== false || strpos($_SERVER['SCRIPT_FILENAME'], '/public/') !== false);
echo "Running from public folder: " . ($inPublicFolder ? 'YES ✅' : 'NO ❌') . "\n\n";

// Test asset URL generation
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
    
    $app = require_once '../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "=== Laravel Configuration ===\n";
    echo "APP_URL: " . config('app.url') . "\n";
    echo "APP_ENV: " . config('app.env') . "\n\n";
    
    echo "=== Asset URL Test ===\n";
    $testPath = 'storage/designCollageImages/test.png';
    $assetUrl = asset($testPath);
    echo "asset('storage/...') generates: $assetUrl\n";
    echo "Should NOT contain '/public/' ❌\n";
    echo "Contains /public/: " . (strpos($assetUrl, '/public/') !== false ? 'YES ❌ WRONG!' : 'NO ✅ GOOD!') . "\n";
}

echo "\n=== PROBLEM ===\n";
echo "Your URLs have '/public/' in them.\n";
echo "This means Apache DocumentRoot is pointing to the wrong folder.\n\n";

echo "=== SOLUTION ===\n";
echo "Apache should point to: C:\\xampp\\htdocs\\piclicks\\public\n";
echo "NOT to: C:\\xampp\\htdocs\\piclicks\n\n";

echo "Fix this in Apache httpd.conf or create a .htaccess file.\n";

echo "</pre>";
?>

