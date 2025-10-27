<?php
echo "<h2>Trace Save & Download Flow</h2><pre>";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=sanshaco_piclicks_live', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Find order #29305
$stmt = $pdo->query("
    SELECT o.id, o.internal_order_id, o.created_at,
           od.collage_unique_id
    FROM orders o
    JOIN order_detail od ON o.id = od.order_id
    WHERE o.internal_order_id = '29305'
    LIMIT 1
");

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "❌ Order #29305 not found!\n";
    echo "\nSearching for most recent order...\n";
    
    $stmt = $pdo->query("
        SELECT o.id, o.internal_order_id, o.created_at,
               od.collage_unique_id
        FROM orders o
        JOIN order_detail od ON o.id = od.order_id
        ORDER BY o.created_at DESC
        LIMIT 1
    ");
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($order) {
    echo "=== FOUND ORDER ===\n";
    echo "Order ID: #{$order['internal_order_id']}\n";
    echo "Database ID: {$order['id']}\n";
    echo "Created: {$order['created_at']}\n";
    echo "Collage unique_id: {$order['collage_unique_id']}\n\n";
    
    // Check tiles for this collage
    $stmt = $pdo->prepare("
        SELECT id, seq, image_edited, 
               CASE 
                   WHEN image_with_bleed IS NULL THEN 'NULL'
                   WHEN image_with_bleed = '' THEN 'EMPTY'
                   ELSE SUBSTRING(image_with_bleed, 1, 80)
               END as bleed_preview,
               empty, is_deleted
        FROM design_collage
        WHERE unique_id = ?
        ORDER BY seq
    ");
    $stmt->execute([$order['collage_unique_id']]);
    $tiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== TILES IN DATABASE ===\n";
    echo "Total tiles: " . count($tiles) . "\n";
    
    $validTiles = 0;
    $tilesWithPrintFiles = 0;
    
    foreach ($tiles as $tile) {
        if ($tile['empty'] == 0 && $tile['is_deleted'] == 0) {
            $validTiles++;
            if ($tile['bleed_preview'] != 'NULL' && $tile['bleed_preview'] != 'EMPTY') {
                $tilesWithPrintFiles++;
            }
        }
    }
    
    echo "Valid tiles (empty=0, deleted=0): $validTiles\n";
    echo "Tiles WITH print files: $tilesWithPrintFiles\n";
    echo "Tiles WITHOUT print files: " . ($validTiles - $tilesWithPrintFiles) . "\n\n";
    
    echo "=== FIRST 5 TILES ===\n";
    $count = 0;
    foreach ($tiles as $tile) {
        if ($tile['empty'] == 0 && $tile['is_deleted'] == 0) {
            echo "Tile {$tile['id']} (seq {$tile['seq']}):\n";
            echo "  image_edited: {$tile['image_edited']}\n";
            echo "  image_with_bleed: {$tile['bleed_preview']}\n\n";
            $count++;
            if ($count >= 5) break;
        }
    }
    
    echo "=== THE PROBLEM ===\n";
    if ($tilesWithPrintFiles == 0) {
        echo "❌ image_with_bleed is NULL for all tiles!\n";
        echo "This is why download shows 'No valid images found'\n\n";
        echo "=== WHY THIS HAPPENS ===\n";
        echo "When user clicks Preview:\n";
        echo "1. CollageServices->saveCollage() runs\n";
        echo "2. Should call generatePrintFilesForCollage()\n";
        echo "3. Should populate image_with_bleed field\n";
        echo "4. BUT it's not working!\n\n";
        echo "Possible reasons:\n";
        echo "- Code not deployed to XAMPP correctly\n";
        echo "- Type parameter not 'preview' or 'manual_admin'\n";
        echo "- Generation fails silently\n";
        echo "- Files saved but database not updated\n";
    } else {
        echo "✅ Some tiles have print files!\n";
        echo "Download should work.\n";
    }
}

echo "</pre>";
?>

