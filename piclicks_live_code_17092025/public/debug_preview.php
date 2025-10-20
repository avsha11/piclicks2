<?php
/**
 * Debug script to check why preview is failing
 * Access via: http://localhost:8000/debug_preview.php?id=853815965
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Illuminate\Support\Facades\DB;

$unique_id = $_GET['id'] ?? '853815965';

echo "<h1>Preview Debug for Collage ID: {$unique_id}</h1>";
echo "<hr>";

// Check database connection
try {
    DB::connection()->getPdo();
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Database connection: FAILED - " . $e->getMessage() . "</p>";
    exit;
}

// Check master record
echo "<h2>1. Checking Master Record</h2>";
$master = DB::table('design_collage_masters')
    ->where('unique_id', $unique_id)
    ->where('status', 0)
    ->first();

if ($master) {
    echo "<p style='color: green;'>✅ Master record found</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ((array)$master as $key => $value) {
        if ($key === 'image_path') {
            $fullPath = realpath(__DIR__ . '/../storage/app/public/') . '/' . $value;
            $exists = file_exists($fullPath) ? '✅ EXISTS' : '❌ MISSING';
            echo "<tr><td><strong>{$key}</strong></td><td>{$value}<br><small>Full path: {$fullPath}</small><br><strong>{$exists}</strong></td></tr>";
        } else {
            echo "<tr><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Master record NOT FOUND</p>";
    echo "<p>Looking for: unique_id = '{$unique_id}' AND status = 0</p>";
    
    // Check if exists with different status
    $anyMaster = DB::table('design_collage_masters')
        ->where('unique_id', $unique_id)
        ->first();
    
    if ($anyMaster) {
        echo "<p style='color: orange;'>⚠️ Found record with different status: {$anyMaster->status}</p>";
    } else {
        echo "<p style='color: red;'>❌ No record found with this unique_id at all</p>";
    }
}

// Check images
echo "<hr>";
echo "<h2>2. Checking Images</h2>";
$images = DB::table('design_collage_images')
    ->where('unique_id', $unique_id)
    ->where('is_deleted', 0)
    ->orderBy('seq', 'asc')
    ->get();

if ($images->count() > 0) {
    echo "<p style='color: green;'>✅ Found {$images->count()} images</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Seq</th><th>Image Path</th><th>File Exists?</th></tr>";
    foreach ($images as $img) {
        $fullPath = realpath(__DIR__ . '/../storage/app/public/') . '/' . $img->image_path;
        $exists = file_exists($fullPath);
        $existsText = $exists ? '✅ Yes' : '❌ No';
        $color = $exists ? 'green' : 'red';
        echo "<tr>";
        echo "<td>{$img->id}</td>";
        echo "<td>{$img->seq}</td>";
        echo "<td><small>{$img->image_path}</small></td>";
        echo "<td style='color: {$color};'>{$existsText}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No images found</p>";
    echo "<p>Looking for: unique_id = '{$unique_id}' AND is_deleted = 0</p>";
}

// Check Imagick
echo "<hr>";
echo "<h2>3. Checking Imagick Extension</h2>";
if (extension_loaded('imagick')) {
    echo "<p style='color: green;'>✅ Imagick is installed</p>";
} else {
    echo "<p style='color: red;'>❌ Imagick is NOT installed</p>";
    echo "<p><strong>This is likely causing the redirect!</strong></p>";
}

// Check preview background images
echo "<hr>";
echo "<h2>4. Checking Preview Background Images</h2>";
$previewImages = [
    'Living Room' => __DIR__ . '/assets/images/preview_livingroom.png',
    'Kitchen' => __DIR__ . '/assets/images/preview_kitchen.png',
];

foreach ($previewImages as $name => $path) {
    $exists = file_exists($path);
    $existsText = $exists ? '✅ EXISTS' : '❌ MISSING';
    $color = $exists ? 'green' : 'red';
    echo "<p style='color: {$color};'>{$existsText} {$name}: <code>{$path}</code></p>";
}

// Check temp directory
echo "<hr>";
echo "<h2>5. Checking Temp Directory</h2>";
$tempDir = realpath(__DIR__ . '/../storage/app/public/temp') ?: __DIR__ . '/../storage/app/public/temp';
if (file_exists($tempDir)) {
    echo "<p style='color: green;'>✅ Temp directory exists: {$tempDir}</p>";
    if (is_writable($tempDir)) {
        echo "<p style='color: green;'>✅ Temp directory is writable</p>";
    } else {
        echo "<p style='color: red;'>❌ Temp directory is NOT writable</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Temp directory doesn't exist yet: {$tempDir}</p>";
    echo "<p>(Will be created automatically)</p>";
}

// Check Laravel logs
echo "<hr>";
echo "<h2>6. Recent Laravel Logs</h2>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -30); // Last 30 lines
    
    echo "<p>Last 30 lines from laravel.log:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 11px;'>";
    foreach ($recentLogs as $line) {
        if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
            echo "<span style='color: red;'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p>No log file found at: {$logFile}</p>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>To fix preview issues:</strong></p>";
echo "<ol>";
if (!extension_loaded('imagick')) {
    echo "<li style='color: red;'><strong>INSTALL IMAGICK</strong> - This is the most likely issue</li>";
}
if (!isset($master)) {
    echo "<li style='color: red;'><strong>Master record missing or has wrong status</strong></li>";
}
if (!isset($images) || $images->count() === 0) {
    echo "<li style='color: red;'><strong>No images found for this collage</strong></li>";
}
echo "<li>Check the Laravel logs above for specific error messages</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='check_imagick.php'>→ Go to Imagick Diagnostics</a></p>";
echo "<p><a href='preview-design-collage/{$unique_id}'>→ Try Preview Again</a></p>";

