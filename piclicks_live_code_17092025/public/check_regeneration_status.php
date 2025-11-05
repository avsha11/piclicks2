<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$uniqueId = $_GET['unique_id'] ?? '525193981';

echo "<h2>Regeneration Status for unique_id: {$uniqueId}</h2>";

// Check tiles
$tiles = DB::table('design_collage')
    ->where('unique_id', $uniqueId)
    ->where('empty', 0)
    ->where('is_deleted', 0)
    ->get(['id', 'seq', 'image_with_bleed', 'updated_at']);

$totalPrintFiles = 0;
$missingCount = 0;
$tilesWithFiles = 0;

foreach ($tiles as $tile) {
    $bleed = json_decode($tile->image_with_bleed, true);
    if (is_array($bleed) && !empty($bleed)) {
        $tilesWithFiles++;
        foreach ($bleed as $path) {
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                $totalPrintFiles++;
            } else {
                $missingCount++;
            }
        }
    } else {
        $missingCount++;
    }
}

echo "<div style='padding: 20px; margin: 20px 0; border: 2px solid " . ($missingCount > 0 ? "#ff9800" : "#4caf50") . "; background: " . ($missingCount > 0 ? "#fff3e0" : "#e8f5e9") . ";'>";
echo "<h3>Status:</h3>";
echo "<p><strong>Total tiles:</strong> {$tiles->count()}</p>";
echo "<p><strong>Tiles with print files:</strong> {$tilesWithFiles}</p>";
echo "<p><strong>Print files generated:</strong> {$totalPrintFiles}</p>";
echo "<p><strong>Missing files:</strong> {$missingCount}</p>";

if ($missingCount > 0) {
    echo "<p style='color: #e65100;'><strong>⚠️ Still generating...</strong></p>";
    echo "<p>Refresh this page in a few seconds.</p>";
    echo "<script>setTimeout(() => window.location.reload(), 5000);</script>";
} else {
    echo "<p style='color: #2e7d32;'><strong>✓ ALL PRINT FILES GENERATED!</strong></p>";
    echo "<p><a href='http://localhost:8000/admin' style='padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; font-size: 16px;'>Go to Admin Panel</a></p>";
    echo "<p>Find order #66570 and click 'Download ZIP'</p>";
}

echo "</div>";

// Show recent log entries
echo "<h3>Recent Log Entries:</h3>";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -30);
    $relevantLines = array_filter($recentLines, function($line) use ($uniqueId) {
        return strpos($line, $uniqueId) !== false || 
               strpos($line, 'GeneratePrintFilesJob') !== false ||
               strpos($line, 'Print files') !== false;
    });
    
    if (!empty($relevantLines)) {
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 300px;'>";
        echo htmlspecialchars(implode('', array_slice($relevantLines, -10)));
        echo "</pre>";
    } else {
        echo "<p>No relevant log entries found yet.</p>";
    }
}








