<?php
/**
 * Fix Database URLs - Update localhost/piclicks to localhost:8000
 * Run once: http://localhost:8000/fix_database_urls.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

echo "<h1>Fixing Database URLs</h1>";
echo "<p>Updating all localhost/piclicks references to localhost:8000...</p>";

$tables = [
    'design_collage' => ['image', 'image_edited', 'image_original', 'image_with_bleed'],
    'design_collage_master' => ['image_path'],
    'orders' => [],
    'users' => [],
];

$totalUpdated = 0;

foreach ($tables as $table => $columns) {
    if (!Schema::hasTable($table)) {
        echo "<p>⚠️ Table $table does not exist - skipping</p>";
        continue;
    }
    
    echo "<h3>Checking table: $table</h3>";
    
    // If no columns specified, get all text/varchar columns
    if (empty($columns)) {
        $columns = [];
        $tableColumns = DB::select("SHOW COLUMNS FROM $table");
        foreach ($tableColumns as $col) {
            if (preg_match('/(char|text)/i', $col->Type)) {
                $columns[] = $col->Field;
            }
        }
    }
    
    foreach ($columns as $column) {
        if (!Schema::hasColumn($table, $column)) {
            echo "<p>⚠️ Column $column does not exist in $table - skipping</p>";
            continue;
        }
        
        // Count records with old URL
        $count = DB::table($table)
            ->where($column, 'like', '%localhost/piclicks%')
            ->count();
        
        if ($count > 0) {
            echo "<p>Found $count records in <strong>$table.$column</strong> with old URLs</p>";
            
            // Update
            DB::table($table)
                ->where($column, 'like', '%localhost/piclicks%')
                ->update([
                    $column => DB::raw("REPLACE($column, 'http://localhost/piclicks/', 'http://localhost:8000/')")
                ]);
            
            $totalUpdated += $count;
            echo "<p>✅ Updated $count records in $table.$column</p>";
        }
    }
}

echo "<hr>";
echo "<h2>✅ COMPLETE!</h2>";
echo "<p>Total records updated: <strong>$totalUpdated</strong></p>";

if ($totalUpdated === 0) {
    echo "<p style='color: green;'>🎉 Your database is already clean! No old URLs found.</p>";
} else {
    echo "<p style='color: green;'>🎉 All URLs have been updated to http://localhost:8000</p>";
}

echo "<hr>";
echo "<p><strong>IMPORTANT:</strong> Always use <code>http://localhost:8000</code> for this project!</p>";
echo "<p><a href='http://localhost:8000/admin-panel'>→ Go to Admin Panel</a></p>";
echo "<p><a href='http://localhost:8000'>→ Go to Homepage</a></p>";

