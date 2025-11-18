<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$svc = new App\Services\PrintFileService();
$props = ['pxPerMm','clearTileWPx','clearTileHPx','printTileWPx','printTileHPx','bleedPx','framePrintThicknessPx'];
$ref = new ReflectionClass($svc);
foreach($props as $name){
    $prop = $ref->getProperty($name);
    $prop->setAccessible(true);
    echo $name.'='.$prop->getValue($svc)."\n";
}
?>
