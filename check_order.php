<?php
// Check specific order
$order_id = 355943226; // The order from logs

echo "<h2>Checking Order: $order_id</h2><pre>";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=sanshaco_piclicks_live', 'root', '');

// Get tiles for this order
$stmt = $pdo->prepare("
    SELECT id, seq, image, image_edited, 
           CASE 
               WHEN image_with_bleed IS NULL THEN 'NULL'
               ELSE SUBSTRING(image_with_bleed, 1, 100)
           END as bleed_preview,
           empty, is_deleted
    FROM design_collage 
    WHERE unique_id = ?
    ORDER BY seq
");
$stmt->execute([$order_id]);
$tiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total tiles found: " . count($tiles) . "\n\n";

foreach ($tiles as $tile) {
    echo "ID: {$tile['id']} | Seq: {$tile['seq']}\n";
    echo "  empty: {$tile['empty']} | is_deleted: {$tile['is_deleted']}\n";
    echo "  image: {$tile['image']}\n";
    echo "  image_edited: {$tile['image_edited']}\n";
    echo "  image_with_bleed: {$tile['bleed_preview']}\n\n";
}

// Count non-empty, non-deleted
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count
    FROM design_collage 
    WHERE unique_id = ? AND empty = 0 AND is_deleted = 0
");
$stmt->execute([$order_id]);
$validCount = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\n=== SUMMARY ===\n";
echo "Valid tiles (empty=0, is_deleted=0): {$validCount['count']}\n";
echo "These should be processed for print files.\n";

echo "</pre>";
?>

