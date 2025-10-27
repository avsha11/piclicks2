<?php
echo "<h2>Find Today's Order Files</h2><pre>";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=sanshaco_piclicks_live', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Find orders created today (Oct 26, 2025)
$stmt = $pdo->query("
    SELECT DISTINCT dc.unique_id, COUNT(*) as tile_count,
           dcm.total_tiles, dcm.image_path,
           dcm.created_at
    FROM design_collage dc
    JOIN design_collage_master dcm ON dc.unique_id = dcm.unique_id
    WHERE DATE(dc.created_at) = '2025-10-26'
    AND dc.empty = 0 AND dc.is_deleted = 0
    GROUP BY dc.unique_id
    ORDER BY dcm.created_at DESC
");

$todaysOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Orders Created Today (Oct 26) ===\n";
echo "Found: " . count($todaysOrders) . " orders\n\n";

foreach ($todaysOrders as $order) {
    echo "unique_id: {$order['unique_id']}\n";
    echo "  Tiles in DB: {$order['tile_count']}\n";
    echo "  Total tiles (master): {$order['total_tiles']}\n";
    echo "  Created: {$order['created_at']}\n";
    echo "  Preview image: " . ($order['image_path'] ?? 'NULL') . "\n\n";
    
    // Check tiles for this order
    $stmt2 = $pdo->prepare("
        SELECT id, seq, image_edited, 
               CASE 
                   WHEN image_with_bleed IS NULL THEN 'NULL'
                   WHEN image_with_bleed = '' THEN 'EMPTY'
                   ELSE 'HAS DATA'
               END as has_print_files
        FROM design_collage
        WHERE unique_id = ? AND empty = 0 AND is_deleted = 0
        ORDER BY seq
        LIMIT 5
    ");
    $stmt2->execute([$order['unique_id']]);
    $tiles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  Sample tiles:\n";
    foreach ($tiles as $tile) {
        echo "    Tile {$tile['id']} (seq {$tile['seq']}): {$tile['has_print_files']}\n";
    }
    echo "\n";
}

// Check for files with today's timestamp
$basePath = 'C:\xampp\htdocs\piclicks\storage\app\public\designCollageImages';
$allFiles = glob($basePath . '\tile_*.png');

echo "\n=== Recent Print Files (Last 20) ===\n";
usort($allFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

for ($i = 0; $i < min(20, count($allFiles)); $i++) {
    $file = $allFiles[$i];
    $time = date('Y-m-d H:i:s', filemtime($file));
    $size = round(filesize($file) / 1024 / 1024, 2);
    echo basename($file) . " - {$size}MB - $time\n";
}

echo "</pre>";
?>

