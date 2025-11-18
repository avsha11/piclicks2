<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$masters = App\Models\DesignCollageMaster::orderBy('updated_at','desc')->take(5)->get(['unique_id','updated_at'])->toArray();
foreach($masters as $m){
    echo $m['unique_id']." " . $m['updated_at']."\n";
}
?>
