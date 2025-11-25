<?php
/**
 * Check which image files are missing for a collage
 * Usage: php check_missing_images.php <unique_id>
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$uniqueId = $argv[1] ?? '117535706';

echo "=== Checking Missing Images for Collage: {$uniqueId} ===\n\n";

// Get tiles for this collage
$tiles = App\Models\DesignCollageModel::where('unique_id', $uniqueId)
    ->where('empty', 0)
    ->where('is_deleted', 0)
    ->get();

if ($tiles->isEmpty()) {
    echo "No tiles found for collage {$uniqueId}\n";
    exit(1);
}

echo "Found {$tiles->count()} tiles\n\n";

$missingFiles = [];
$existingFiles = [];
$basePath = storage_path('app/public/');

foreach ($tiles as $tile) {
    // Check image_edited
    $imageEdited = $tile->image_edited;
    if (!empty($imageEdited)) {
        $fullPath = $basePath . $imageEdited;
        if (file_exists($fullPath)) {
            $existingFiles[] = $imageEdited;
            echo "✓ {$imageEdited}\n";
        } else {
            $missingFiles[] = $imageEdited;
            echo "✗ MISSING: {$imageEdited}\n";
        }
    }
    
    // Check image (original)
    $image = $tile->image;
    if (!empty($image) && $image !== $imageEdited) {
        $fullPath = $basePath . $image;
        if (file_exists($fullPath)) {
            $existingFiles[] = $image;
            echo "✓ {$image}\n";
        } else {
            $missingFiles[] = $image;
            echo "✗ MISSING: {$image}\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Existing files: " . count($existingFiles) . "\n";
echo "Missing files: " . count($missingFiles) . "\n";

if (!empty($missingFiles)) {
    echo "\n=== Missing Files ===\n";
    foreach ($missingFiles as $file) {
        echo "  - {$file}\n";
    }
    
    // Check if files exist with similar names
    echo "\n=== Checking for similar filenames ===\n";
    $dir = storage_path('app/public/designCollageImages/');
    if (is_dir($dir)) {
        $allFiles = scandir($dir);
        foreach ($missingFiles as $missing) {
            $basename = basename($missing);
            $pattern = substr($basename, 0, 10); // First 10 chars (timestamp)
            $matches = array_filter($allFiles, function($f) use ($pattern) {
                return strpos($f, $pattern) === 0;
            });
            if (!empty($matches)) {
                echo "  Found similar files for {$basename}:\n";
                foreach (array_slice($matches, 0, 3) as $match) {
                    echo "    - {$match}\n";
                }
            }
        }
    }
}

