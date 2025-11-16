<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$data = App\Models\DesignCollageModel::where('unique_id','646631868')->orderBy('seq')->get()->toArray();
file_put_contents('tile_dump.json', json_encode($data, JSON_PRETTY_PRINT));
?>
