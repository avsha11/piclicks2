<?php
/**
 * Debug helper: dump tile span/position data for a collage unique_id.
 * Usage:
 *   php dump_collage_tiles.php 646631868
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$uniqueId = $argv[1] ?? null;
if (!$uniqueId) {
    fwrite(STDERR, "Usage: php dump_collage_tiles.php <unique_id>\n");
    exit(1);
}

$tiles = App\Models\DesignCollageModel::where('unique_id', $uniqueId)
    ->where('empty', 0)
    ->where('is_deleted', 0)
    ->orderBy('seq')
    ->get();

foreach ($tiles as $tile) {
    $other = json_decode($tile->other_settings, true) ?: [];
    $imageMargin = $other['imageDivDataMargin'] ?? '';
    $style = $other['imageDivStyle'] ?? '';
    echo "Tile {$tile->id} seq {$tile->seq}\n";
    echo "  margin: {$imageMargin}\n";
    echo "  style: {$style}\n";
    echo "  zoom: " . ($other['zoom'] ?? '') . "\n";
    echo "  rotate: " . ($other['rotate'] ?? '') . "\n";
}

