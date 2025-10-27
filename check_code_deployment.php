<?php
echo "<h2>Check Code Deployment in XAMPP</h2><pre>";

// Check if code files exist
$files = [
    'CollageServices' => 'C:\xampp\htdocs\piclicks\app\Services\CollageServices.php',
    'PrintFileService' => 'C:\xampp\htdocs\piclicks\app\Services\PrintFileService.php',
    'OrderController' => 'C:\xampp\htdocs\piclicks\app\Http\Controllers\Admin\OrderController.php'
];

foreach ($files as $name => $path) {
    $exists = file_exists($path);
    echo "$name: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "\n";
    
    if ($exists) {
        $content = file_get_contents($path);
        $size = round(strlen($content) / 1024, 1);
        echo "  Size: {$size} KB\n";
        
        if ($name == 'CollageServices') {
            echo "  Has PrintFileService import: " . (strpos($content, 'use App\Services\PrintFileService') !== false ? 'YES ✅' : 'NO ❌') . "\n";
            echo "  Has generatePrintFilesForCollage method: " . (strpos($content, 'function generatePrintFilesForCollage') !== false ? 'YES ✅' : 'NO ❌') . "\n";
            echo "  Has preview type check: " . (strpos($content, "in_array(\$request->type, ['preview'") !== false ? 'YES ✅' : 'NO ❌') . "\n";
        }
        
        if ($name == 'PrintFileService') {
            echo "  Has generatePrintFiles method: " . (strpos($content, 'function generatePrintFiles') !== false ? 'YES ✅' : 'NO ❌') . "\n";
            echo "  Saves as PNG: " . (strpos($content, 'imagepng(') !== false ? 'YES ✅' : 'NO ❌') . "\n";
        }
    }
    echo "\n";
}

// Check storage path configuration
echo "=== STORAGE PATHS ===\n";
echo "storage_path(): This would be set by Laravel\n";
echo "Expected: C:\\xampp\\htdocs\\piclicks\\storage\\app\\public\n";
echo "Actual check: " . (is_dir('C:\xampp\htdocs\piclicks\storage\app\public\designCollageImages') ? 'EXISTS ✅' : 'MISSING ❌') . "\n\n";

echo "=== THE DISCONNECT ===\n";
echo "Files are being saved somewhere (you can see them).\n";
echo "But database image_with_bleed is NULL.\n\n";
echo "This means ONE of these is true:\n";
echo "1. Code is running but database UPDATE fails silently\n";
echo "2. Code is not running (type != 'preview')\n";
echo "3. Code generates files but doesn't update database\n";
echo "4. Files are saved to Dropbox, not XAMPP\n";

echo "</pre>";
?>

