<?php
echo "<h2>Find Oct 24 Order with Print Files</h2><pre>";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=sanshaco_piclicks_live', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// The print files have timestamp 176131370x (Oct 24, 15:48)
// Let's find tiles created around that time

$stmt = $pdo->query("
    SELECT unique_id, COUNT(*) as tile_count,
           MIN(created_at) as first_tile,
           MAX(created_at) as last_tile
    FROM design_collage
    WHERE empty = 0 AND is_deleted = 0
    AND created_at BETWEEN '2025-10-24 14:00:00' AND '2025-10-24 17:00:00'
    GROUP BY unique_id
    ORDER BY first_tile DESC
");

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Orders Created Oct 24 (14:00-17:00) ===\n";
echo "Found: " . count($orders) . " orders\n\n";

foreach ($orders as $order) {
    echo "unique_id: {$order['unique_id']}\n";
    echo "  Tiles: {$order['tile_count']}\n";
    echo "  Created: {$order['first_tile']} to {$order['last_tile']}\n";
    
    // Get a sample tile to see timestamp
    $stmt2 = $pdo->prepare("
        SELECT id, seq, image_edited, image_with_bleed
        FROM design_collage
        WHERE unique_id = ? AND empty = 0 AND is_deleted = 0
        ORDER BY seq
        LIMIT 3
    ");
    $stmt2->execute([$order['unique_id']]);
    $tiles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  Sample tiles:\n";
    foreach ($tiles as $tile) {
        $hasBleed = ($tile['image_with_bleed'] && $tile['image_with_bleed'] != 'NULL') ? 'YES' : 'NULL';
        echo "    {$tile['id']}: {$tile['image_edited']} | bleed: $hasBleed\n";
    }
    echo "\n";
}

// Also check for files matching timestamp 1761313xxx
$basePath = 'C:\xampp\htdocs\piclicks\storage\app\public\designCollageImages';
$files = glob($basePath . '\tile_17613137*.png');
echo "\n=== Print Files (timestamp 17613137xx) ===\n";
echo "Files found: " . count($files) . "\n";
foreach ($files as $file) {
    echo basename($file) . "\n";
}

echo "\n=== ACTION NEEDED ===\n";
if (count($orders) > 0 && count($files) > 0) {
    echo "We need to link print files to unique_id: {$orders[0]['unique_id']}\n";
    echo "This will populate image_with_bleed field.\n";
} else {
    echo "❌ Cannot find matching order for these print files.\n";
    echo "You may need to create a NEW test order.\n";
}

echo "</pre>";
?>

