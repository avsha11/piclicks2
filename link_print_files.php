<?php
echo "<h2>Link Print Files to Database</h2><pre>";

// Connect to database
$pdo = new PDO('mysql:host=127.0.0.1;dbname=sanshaco_piclicks_live', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Find tiles created around Oct 24 that match the print files timestamp
$basePath = 'C:\xampp\htdocs\piclicks\storage\app\public\designCollageImages';

// Get Oct 24 print files
$files = glob($basePath . '\tile_1761309*.png');
echo "Oct 24 print files found: " . count($files) . "\n\n";

if (count($files) > 0) {
    // These files were created at timestamp 1761309xxx
    // Let's find tiles with image_edited around that time
    
    $stmt = $pdo->query("
        SELECT unique_id, COUNT(*) as tile_count
        FROM design_collage
        WHERE empty = 0 AND is_deleted = 0
        AND image_edited LIKE '%1761309%'
        GROUP BY unique_id
        ORDER BY unique_id DESC
    ");
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Orders with Oct 24 tiles ===\n";
    foreach ($orders as $order) {
        echo "unique_id: {$order['unique_id']} | tiles: {$order['tile_count']}\n";
    }
    
    if (count($orders) > 0) {
        $targetOrder = $orders[0]['unique_id'];
        echo "\n=== Processing Order: $targetOrder ===\n";
        
        // Get tiles for this order
        $stmt = $pdo->prepare("
            SELECT id, seq, image_edited, image_with_bleed
            FROM design_collage
            WHERE unique_id = ? AND empty = 0 AND is_deleted = 0
            ORDER BY seq
        ");
        $stmt->execute([$targetOrder]);
        $tiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Tiles in database: " . count($tiles) . "\n";
        echo "Print files available: " . count($files) . "\n\n";
        
        // Try to match print files to tiles
        foreach ($tiles as $tile) {
            echo "Tile ID {$tile['id']} (seq {$tile['seq']}):\n";
            echo "  image_edited: {$tile['image_edited']}\n";
            echo "  image_with_bleed: " . ($tile['image_with_bleed'] ?? 'NULL') . "\n";
            
            // Find matching print file
            $matchingFiles = [];
            foreach ($files as $file) {
                if (strpos(basename($file), '_' . $tile['seq'] . '_') !== false || 
                    strpos(basename($file), 'tile_' . $tile['seq'] . '_') !== false) {
                    $matchingFiles[] = basename($file);
                }
            }
            
            if (count($matchingFiles) > 0) {
                echo "  Possible match: " . $matchingFiles[0] . "\n";
            } else {
                echo "  No matching print file found\n";
            }
            echo "\n";
        }
    }
} else {
    echo "No Oct 24 print files found!\n";
}

echo "</pre>";
?>

