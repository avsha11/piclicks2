<?php
/**
 * Debug helper: dump text_editor payload for a given collage unique_id.
 * Usage:
 *   php dump_text_overlay.php 646631868
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$uniqueId = $argv[1] ?? null;
if (!$uniqueId) {
    fwrite(STDERR, "Usage: php dump_text_overlay.php <unique_id>\n");
    exit(1);
}

$master = App\Models\DesignCollageMaster::where('unique_id', $uniqueId)->first();
if (!$master) {
    fwrite(STDERR, "Collage {$uniqueId} not found.\n");
    exit(1);
}

echo $master->text_editor ?? '';


