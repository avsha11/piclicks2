<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$uniqueId = $_GET['unique_id'] ?? '725989432';

echo "<h2>Database Path Diagnosis: {$uniqueId}</h2>";

// Get tiles
$tiles = DB::table('design_collage')
    ->where('unique_id', $uniqueId)
    ->where('empty', 0)
    ->where('is_deleted', 0)
    ->orderBy('seq')
    ->get(['id', 'seq', 'image_with_bleed', 'image_edited']);

echo "<p><strong>Found {$tiles->count()} tiles</strong></p>";

$storageDir = storage_path('app/public/designCollageImages/');
echo "<p><strong>Storage directory:</strong> {$storageDir}</p>";

echo "<h3>Tile-by-Tile Analysis:</h3>";

foreach ($tiles as $tile) {
    echo "<div style='border: 2px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9;'>";
    echo "<h4>Tile Seq #{$tile->seq}</h4>";
    
    // Show raw data
    echo "<p><strong>image_with_bleed (raw):</strong></p>";
    echo "<pre style='background: #fff; padding: 10px; overflow: auto;'>" . htmlspecialchars($tile->image_with_bleed) . "</pre>";
    
    // Try to decode
    $bleedData = json_decode($tile->image_with_bleed, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($bleedData)) {
        echo "<p><strong>Decoded as JSON array:</strong> " . count($bleedData) . " files</p>";
        
        foreach ($bleedData as $idx => $path) {
            echo "<div style='margin-left: 20px; padding: 5px; background: #e8f5e9;'>";
            echo "<p><strong>File #{$idx}:</strong> " . htmlspecialchars($path) . "</p>";
            
            // Extract just the filename
            $filename = basename($path);
            $fullPath = $storageDir . $filename;
            
            echo "<p>Filename: <code>{$filename}</code></p>";
            echo "<p>Full path: <code>{$fullPath}</code></p>";
            
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
                echo "<p style='color: green;'>✓ File EXISTS on disk (" . round($size/1024/1024, 2) . " MB)</p>";
            } else {
                echo "<p style='color: red;'>✗ File MISSING from disk</p>";
                
                // Check if it's using a different path format
                $altPath1 = storage_path('app/public/' . $path);
                $altPath2 = public_path('storage/' . $filename);
                
                if (file_exists($altPath1)) {
                    echo "<p style='color: orange;'>Found at: {$altPath1}</p>";
                } elseif (file_exists($altPath2)) {
                    echo "<p style='color: orange;'>Found at: {$altPath2}</p>";
                }
            }
            
            echo "</div>";
        }
    } else {
        echo "<p><strong>Not JSON or invalid:</strong></p>";
        
        if (!empty($tile->image_with_bleed)) {
            // Treat as single path
            $filename = basename($tile->image_with_bleed);
            $fullPath = $storageDir . $filename;
            
            echo "<p>Filename: <code>{$filename}</code></p>";
            echo "<p>Full path: <code>{$fullPath}</code></p>";
            
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
                echo "<p style='color: green;'>✓ File EXISTS on disk (" . round($size/1024/1024, 2) . " MB)</p>";
            } else {
                echo "<p style='color: red;'>✗ File MISSING from disk</p>";
            }
        } else {
            echo "<p style='color: orange;'>Empty/NULL</p>";
        }
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p>If files exist but ZIP download fails, check OrderController path resolution logic.</p>";















