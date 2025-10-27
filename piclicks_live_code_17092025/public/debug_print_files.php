<?php
// Diagnostic script to check print file generation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Print File Generation Diagnostic</h1>";
echo "<pre>";

// Check storage paths
echo "=== STORAGE PATHS ===\n";
$basePath = realpath(__DIR__ . '/../storage/app/public/');
echo "Storage base path: $basePath\n";
echo "Storage exists: " . (is_dir($basePath) ? 'YES' : 'NO') . "\n";

$imagesPath = $basePath . '/designCollageImages';
echo "Images path: $imagesPath\n";
echo "Images folder exists: " . (is_dir($imagesPath) ? 'YES' : 'NO') . "\n\n";

// Check PNG print files
echo "=== PNG PRINT FILES ===\n";
$pngFiles = glob($imagesPath . '/tile_*.png');
echo "Total PNG files found: " . count($pngFiles) . "\n";

if (count($pngFiles) > 0) {
    echo "\nRecent 5 PNG files:\n";
    usort($pngFiles, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    for ($i = 0; $i < min(5, count($pngFiles)); $i++) {
        $file = $pngFiles[$i];
        $size = filesize($file);
        $sizeMB = round($size / 1024 / 1024, 2);
        $time = date('Y-m-d H:i:s', filemtime($file));
        echo "  " . basename($file) . " - {$sizeMB} MB - {$time}\n";
    }
}

// Check database
echo "\n=== DATABASE CHECK ===\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sanshaco_piclicks_live', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get most recent design_collage entries
    $stmt = $pdo->query("
        SELECT id, seq, image_edited, 
               CASE 
                   WHEN image_with_bleed IS NULL THEN 'NULL'
                   WHEN image_with_bleed = '' THEN 'EMPTY'
                   ELSE SUBSTRING(image_with_bleed, 1, 80)
               END as image_with_bleed_preview
        FROM design_collage 
        WHERE empty = 0 AND is_deleted = 0 
        ORDER BY id DESC 
        LIMIT 5
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Recent database entries:\n";
    foreach ($results as $row) {
        echo "\nID: {$row['id']} | Seq: {$row['seq']}\n";
        echo "  image_edited: {$row['image_edited']}\n";
        echo "  image_with_bleed: {$row['image_with_bleed_preview']}\n";
    }
    
    // Check how many have NULL image_with_bleed
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN image_with_bleed IS NULL OR image_with_bleed = '' THEN 1 ELSE 0 END) as null_count,
            SUM(CASE WHEN image_with_bleed IS NOT NULL AND image_with_bleed != '' THEN 1 ELSE 0 END) as has_print_files
        FROM design_collage 
        WHERE empty = 0 AND is_deleted = 0
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\n\n=== STATISTICS ===\n";
    echo "Total tiles: {$stats['total']}\n";
    echo "Tiles WITHOUT print files (NULL): {$stats['null_count']}\n";
    echo "Tiles WITH print files: {$stats['has_print_files']}\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== CONCLUSION ===\n";
if (count($pngFiles) > 0 && isset($stats) && $stats['has_print_files'] > 0) {
    echo "✅ PNG files exist AND database has print file paths\n";
    echo "   Download should work for orders with print files.\n";
} elseif (count($pngFiles) > 0 && isset($stats) && $stats['has_print_files'] == 0) {
    echo "⚠️ PNG files exist BUT database image_with_bleed is NULL\n";
    echo "   Print files aren't being saved to database during Preview!\n";
    echo "   This is the problem - fix CollageServices->saveCollage()\n";
} else {
    echo "❌ No PNG files found at all\n";
    echo "   Print file generation is not working.\n";
}

echo "</pre>";
?>

