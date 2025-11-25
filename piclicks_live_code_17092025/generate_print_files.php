<?php
/**
 * Manual Print File Generation Script
 * 
 * Usage: php generate_print_files.php <unique_id>
 * Example: php generate_print_files.php 586455569
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CollageServices;
use App\Repositories\DesignCollageRepository;
use Illuminate\Support\Facades\Log;

if ($argc < 2) {
    echo "Usage: php generate_print_files.php <unique_id>\n";
    echo "Example: php generate_print_files.php 586455569\n";
    exit(1);
}

$unique_id = $argv[1];

echo "=== Generating Print Files for Collage ===\n";
echo "Unique ID: {$unique_id}\n\n";

try {
    $collageService = app(CollageServices::class);
    $collageRepo = app(DesignCollageRepository::class);
    
    // Get master data
    $masterdata = $collageRepo->getOneMaster(['unique_id' => $unique_id]);
    
    if (!$masterdata) {
        echo "ERROR: Collage not found with unique_id: {$unique_id}\n";
        exit(1);
    }
    
    echo "Found collage. Generating print files...\n";
    
    // Generate print files
    $collageService->generatePrintFilesForCollage($unique_id, $masterdata);
    
    echo "\n✅ Print files generation completed!\n";
    echo "Check the logs for details: storage/logs/laravel.log\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}

