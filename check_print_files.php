<?php
echo "<h2>Print File Verification</h2><pre>";

$basePath = 'C:\xampp\htdocs\piclicks\storage\app\public\designCollageImages';
$files = glob($basePath . '\tile_*.png');

echo "Total PNG print files in XAMPP: " . count($files) . "\n\n";

if (count($files) > 0) {
    // Check first 3 files
    echo "=== Checking Dimensions ===\n";
    for ($i = 0; $i < min(3, count($files)); $i++) {
        $file = $files[$i];
        $size = getimagesize($file);
        echo "File: " . basename($file) . "\n";
        echo "  Dimensions: {$size[0]} x {$size[1]} pixels\n";
        echo "  Expected: 1744 x 1535 pixels (with bleed)\n";
        echo "  Has bleed: " . ($size[0] == 1744 && $size[1] == 1535 ? 'YES ✅' : 'NO ❌') . "\n\n";
    }
}

echo "\n=== CONCLUSION ===\n";
if (count($files) > 0) {
    echo "✅ Print files exist in XAMPP!\n";
    echo "Now need to link them in database (image_with_bleed field).\n";
} else {
    echo "❌ No print files in XAMPP\n";
}

echo "</pre>";
?>

