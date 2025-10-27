<?php
echo "<h2>Check New vs Old Print Files</h2><pre>";

$basePath = 'C:\xampp\htdocs\piclicks\storage\app\public\designCollageImages';

// Check files from different time periods
$timeRanges = [
    'Recent (Oct 26, 2025)' => strtotime('2025-10-26'),
    'Oct 24, 2025' => strtotime('2025-10-24'),
    'Oct 22, 2025' => strtotime('2025-10-22'),
    'Older (before Oct 20)' => 0
];

foreach ($timeRanges as $label => $minTime) {
    $files = glob($basePath . '\tile_*.png');
    $matchingFiles = [];
    
    foreach ($files as $file) {
        $fileTime = filemtime($file);
        if ($fileTime >= $minTime) {
            $matchingFiles[] = $file;
        }
    }
    
    if (count($matchingFiles) > 0) {
        $sample = $matchingFiles[0];
        $size = getimagesize($sample);
        $fileDate = date('Y-m-d H:i', filemtime($sample));
        
        echo "$label:\n";
        echo "  Files: " . count($matchingFiles) . "\n";
        echo "  Sample: " . basename($sample) . " ($fileDate)\n";
        echo "  Dimensions: {$size[0]} x {$size[1]} pixels\n";
        echo "  Correct: " . ($size[0] == 1744 && $size[1] == 1535 ? 'YES ✅' : 'NO ❌') . "\n\n";
        
        if ($size[0] == 1744 && $size[1] == 1535) {
            echo "  ✅ These files have CORRECT dimensions!\n\n";
            break;
        }
    }
}

echo "</pre>";
?>

