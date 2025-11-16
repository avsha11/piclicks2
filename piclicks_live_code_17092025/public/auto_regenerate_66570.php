<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$uniqueId = '525193981'; // Order #66570

echo "<h2>Auto-Regenerating Print Files for Order #66570</h2>";
echo "<p><strong>unique_id:</strong> {$uniqueId}</p>";

// Get master data
$master = DB::table('design_collage_master')->where('unique_id', $uniqueId)->first();

if (!$master) {
    echo "<p style='color: red;'>Master data not found!</p>";
    exit;
}

echo "<p><strong>Filter:</strong> {$master->filter}</p>";
echo "<p><strong>Grid:</strong> {$master->grid_columns}×{$master->grid_rows}</p>";

// Dispatch the job
try {
    Log::info("Auto-regeneration triggered for order #66570", [
        'unique_id' => $uniqueId,
        'triggered_by' => 'auto_regenerate_66570.php'
    ]);
    
    App\Jobs\GeneratePrintFilesJob::dispatch($uniqueId, $master);
    
    echo "<div style='background: #e8f5e9; padding: 20px; border: 2px solid #4caf50; margin: 20px 0;'>";
    echo "<h3 style='color: #2e7d32;'>✓ Job Dispatched!</h3>";
    echo "<p>Print files are being generated in the background.</p>";
    echo "<p><strong>Progress:</strong></p>";
    echo "<ol>";
    echo "<li>Wait 60 seconds for generation to complete</li>";
    echo "<li>Then go to <a href='http://localhost:8000/admin'>Admin Panel</a></li>";
    echo "<li>Find order #66570</li>";
    echo "<li>Click 'Download ZIP'</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h3>Checking status in 5 seconds...</h3>";
    echo "<script>setTimeout(() => window.location.href = 'check_regeneration_status.php?unique_id={$uniqueId}', 5000);</script>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 20px; border: 2px solid #f44336;'>";
    echo "<h3 style='color: #c62828;'>✗ Error</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "<p>Line: {$e->getLine()}</p>";
    echo "</div>";
}













