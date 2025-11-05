<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h2>Database Table Structure</h2>";

// Check order_detail columns
echo "<h3>order_detail table columns:</h3>";
$columns = DB::select("DESCRIBE order_detail");
echo "<ul>";
foreach ($columns as $col) {
    echo "<li><strong>{$col->Field}</strong> - {$col->Type}</li>";
}
echo "</ul>";

// Check a sample record
echo "<h3>Sample order_detail record:</h3>";
$sample = DB::table('order_detail')->first();
if ($sample) {
    echo "<pre>";
    print_r($sample);
    echo "</pre>";
} else {
    echo "<p>No records found</p>";
}

// Check orders table
echo "<h3>orders table columns:</h3>";
$ordersColumns = DB::select("DESCRIBE orders");
echo "<ul>";
foreach ($ordersColumns as $col) {
    echo "<li><strong>{$col->Field}</strong> - {$col->Type}</li>";
}
echo "</ul>";








