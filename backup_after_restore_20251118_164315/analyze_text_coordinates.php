<?php
/**
 * Analyze text coordinate system for a collage
 * Usage: php analyze_text_coordinates.php <unique_id>
 * 
 * This script helps understand:
 * 1. What coordinate system the editor uses
 * 2. How text positions relate to the grid vs containers
 * 3. What the "zero point" actually is
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$uniqueId = $argv[1] ?? null;
if (!$uniqueId) {
    fwrite(STDERR, "Usage: php analyze_text_coordinates.php <unique_id>\n");
    exit(1);
}

$master = App\Models\DesignCollageMaster::where('unique_id', $uniqueId)->first();
if (!$master) {
    fwrite(STDERR, "Collage {$uniqueId} not found.\n");
    exit(1);
}

echo "=== TEXT COORDINATE SYSTEM ANALYSIS ===\n\n";

// Get grid dimensions
$gridCols = intval($master->grid_columns ?? 5);
$gridRows = intval($master->grid_rows ?? 5);
echo "Grid: {$gridCols} columns × {$gridRows} rows\n\n";

// Get all tiles/blocks
$tiles = App\Models\DesignCollageModel::where('unique_id', $uniqueId)
    ->where('empty', 0)
    ->where('is_deleted', 0)
    ->orderBy('seq')
    ->get();

echo "=== OCCUPIED BLOCKS/CONTAINERS ===\n";
$editorTileWidth = 91.0;
$editorTileHeight = 80.0;
$editorTileMargin = 2.0;

$blocks = [];
foreach ($tiles as $tile) {
    $other = json_decode($tile->other_settings, true);
    $style = $other['imageDivStyle'] ?? '';
    
    // Parse position
    preg_match('/left:\s*(\d+)px/', $style, $leftMatch);
    preg_match('/top:\s*(\d+)px/', $style, $topMatch);
    $left = intval($leftMatch[1] ?? 0);
    $top = intval($topMatch[1] ?? 0);
    
    // Parse size
    $margin = $other['imageDivDataMargin'] ?? null;
    if (empty($margin) || $margin === 'null') {
        $cols = 1;
        $rows = 1;
    } else {
        $parts = explode('|', $margin);
        // Estimate cols/rows from margin (simplified)
        $cols = max(1, intval($parts[0] ?? 0) / 2 + 1);
        $rows = max(1, intval($parts[1] ?? 0) / 2 + 1);
    }
    
    $blockW = $cols * $editorTileWidth + (max($cols - 1, 0) * $editorTileMargin);
    $blockH = $rows * $editorTileHeight + (max($rows - 1, 0) * $editorTileMargin);
    
    $blocks[] = [
        'id' => $tile->id,
        'seq' => $tile->seq,
        'left' => $left,
        'top' => $top,
        'width' => $blockW,
        'height' => $blockH,
        'cols' => $cols,
        'rows' => $rows,
    ];
    
    echo sprintf(
        "Block %d (seq %d): pos=(%d, %d), size=%dx%d (%dx%d tiles)\n",
        $tile->id, $tile->seq, $left, $top, $blockW, $blockH, $cols, $rows
    );
}

// Calculate content bounding box
if (!empty($blocks)) {
    $minLeft = min(array_column($blocks, 'left'));
    $minTop = min(array_column($blocks, 'top'));
    $maxRight = max(array_map(fn($b) => $b['left'] + $b['width'], $blocks));
    $maxBottom = max(array_map(fn($b) => $b['top'] + $b['height'], $blocks));
    
    echo "\n=== CONTENT BOUNDING BOX ===\n";
    echo sprintf(
        "Top-left: (%d, %d)\nBottom-right: (%d, %d)\nWidth: %dpx, Height: %dpx\n",
        $minLeft, $minTop, $maxRight, $maxBottom,
        $maxRight - $minLeft, $maxBottom - $minTop
    );
}

// Parse text overlays
$textEditorJson = $master->text_editor ?? '[]';
$textOverlays = json_decode($textEditorJson, true) ?? [];

echo "\n=== TEXT OVERLAYS ===\n";
if (empty($textOverlays)) {
    echo "No text overlays found.\n";
} else {
    foreach ($textOverlays as $idx => $overlay) {
        $styles = $overlay['styles'] ?? '';
        $text = $overlay['text'] ?? '';
        
        // Parse position
        preg_match('/left:\s*(\d+)px/', $styles, $leftMatch);
        preg_match('/top:\s*(\d+)px/', $styles, $topMatch);
        preg_match('/transform:[^;]*translate\(([^)]+)\)/', $styles, $translateMatch);
        preg_match('/transform:[^;]*rotate\(([^)]+)\)/', $styles, $rotateMatch);
        
        $left = intval($leftMatch[1] ?? 0);
        $top = intval($topMatch[1] ?? 0);
        $translate = $translateMatch[1] ?? '-50%, -50%';
        $rotate = $rotateMatch[1] ?? '0deg';
        
        echo "\nText #{$idx}: \"{$text}\"\n";
        echo "  CSS position: left={$left}px, top={$top}px\n";
        echo "  Transform: translate({$translate}), rotate({$rotate})\n";
        
        // Calculate effective center (after translate -50%, -50%)
        // Note: We'd need actual text dimensions to calculate this accurately
        echo "  Effective center (estimated): ~({$left}, {$top})px\n";
        
        // Check which blocks this text is over/near
        echo "  Relative to content bounding box: ";
        if (isset($minLeft) && isset($minTop)) {
            $relX = $left - $minLeft;
            $relY = $top - $minTop;
            echo "({$relX}, {$relY})px from content top-left\n";
        } else {
            echo "N/A (no blocks)\n";
        }
        
        // Check which blocks it intersects
        echo "  Intersects blocks: ";
        $intersecting = [];
        foreach ($blocks as $block) {
            // Rough check (would need actual text bounding box for accuracy)
            if ($left >= $block['left'] && $left <= $block['left'] + $block['width'] &&
                $top >= $block['top'] && $top <= $block['top'] + $block['height']) {
                $intersecting[] = $block['id'];
            }
        }
        if (empty($intersecting)) {
            echo "None (or needs bounding box calculation)\n";
        } else {
            echo implode(', ', $intersecting) . "\n";
        }
    }
}

echo "\n=== COORDINATE SYSTEM SUMMARY ===\n";
echo "Grid-based zero point: (0, 0) = top-left of grid container\n";
if (isset($minLeft) && isset($minTop)) {
    echo "Content-based zero point: ({$minLeft}, {$minTop}) = top-left of content bounding box\n";
    echo "Offset: Grid zero is ({$minLeft}, {$minTop})px from content zero\n";
}
echo "\n";

