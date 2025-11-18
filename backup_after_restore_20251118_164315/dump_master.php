<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$master = App\Models\DesignCollageMaster::where('unique_id','646631868')->first();
file_put_contents('master_dump.json', json_encode($master?->toArray(), JSON_PRETTY_PRINT));
?>
