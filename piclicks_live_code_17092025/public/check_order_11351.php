<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h2>Order #11351 Analysis</h2>";

// Find order
$order = DB::table('orders')->where('internal_order_id', '11351')->first();

if ($order) {
    echo "<p><strong>Order created:</strong> {$order->created_at}</p>";
    
    // Find collages updated around the same time
    $updatedTiles = DB::table('design_collage')
        ->where('updated_at', '>=', date('Y-m-d H:i:s', strtotime($order->created_at) - 300))
        ->where('updated_at', '<=', date('Y-m-d H:i:s', strtotime($order->created_at) + 600))
        ->whereNotNull('image_with_bleed')
        ->get(['unique_id', 'updated_at'])
        ->groupBy('unique_id');
    
    if ($updatedTiles->count() > 0) {
        echo "<h3>Found collage:</h3>";
        foreach ($updatedTiles as $uniqueId => $tiles) {
            echo "<div style='background: #e8f5e9; border: 2px solid #4caf50; padding: 15px; margin: 10px 0;'>";
            echo "<h4>unique_id = <strong>{$uniqueId}</strong></h4>";
            echo "<p>Print files were generated at: {$tiles->first()->updated_at}</p>";
            
            // Check if this was before or after fix
            $fileTime = strtotime($tiles->first()->updated_at);
            $fixTime = strtotime('2025-10-29 15:30:00'); // Approximate time of fix
            
            if ($fileTime < $fixTime) {
                echo "<div style='background: #ffebee; padding: 10px; border: 2px solid #f44336;'>";
                echo "<h3 style='color: #c62828;'>⚠️ FILES GENERATED BEFORE FIX</h3>";
                echo "<p><strong>These print files were created with OLD CODE</strong></p>";
                echo "<p>They will have the old text rendering issues.</p>";
                echo "<p><strong>Solution:</strong> You need to:</p>";
                echo "<ol>";
                echo "<li>Go to the collage editor</li>";
                echo "<li>Load this collage (unique_id: {$uniqueId})</li>";
                echo "<li>Click 'Save' then 'Preview'</li>";
                echo "<li>This will regenerate print files with the NEW fixes</li>";
                echo "</ol>";
                echo "</div>";
            } else {
                echo "<div style='background: #e8f5e9; padding: 10px; border: 2px solid #4caf50;'>";
                echo "<h3 style='color: #2e7d32;'>✓ FILES GENERATED AFTER FIX</h3>";
                echo "<p>These print files should have the correct text rendering!</p>";
                echo "</div>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>No collage found for this order!</p>";
    }
} else {
    echo "<p style='color: red;'>Order #11351 not found!</p>";
}













