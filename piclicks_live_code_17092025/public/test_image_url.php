<!DOCTYPE html>
<html>
<head>
    <title>Test Image URL Access</title>
</head>
<body>
    <h2>Testing Image URL Access for Order #93167</h2>
    
    <?php
    $testFile = 'tile_1761745560_69021a980045a.png';
    $storagePath = __DIR__ . '/storage/designCollageImages/' . $testFile;
    $url = '/storage/designCollageImages/' . $testFile;
    ?>
    
    <p><strong>Test File:</strong> <?= $testFile ?></p>
    <p><strong>Storage Path:</strong> <?= $storagePath ?></p>
    <p><strong>File Exists (PHP):</strong> <?= file_exists($storagePath) ? '✓ YES' : '✗ NO' ?></p>
    <p><strong>URL:</strong> <a href="<?= $url ?>" target="_blank"><?= $url ?></a></p>
    
    <h3>Test 1: Direct IMG Tag</h3>
    <img src="<?= $url ?>" style="max-width: 300px; border: 2px solid #ccc;" onerror="this.style.border='2px solid red'; this.alt='FAILED TO LOAD';" />
    
    <h3>Test 2: JavaScript Fetch</h3>
    <button onclick="testFetch()">Test Fetch</button>
    <div id="fetchResult" style="margin-top: 10px; padding: 10px; background: #f0f0f0;"></div>
    
    <h3>Test 3: Check All Files for Order</h3>
    <?php
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $tiles = DB::table('design_collage')
        ->where('unique_id', '725989432')
        ->where('empty', 0)
        ->where('is_deleted', 0)
        ->orderBy('seq')
        ->get(['seq', 'image_with_bleed']);
    
    echo "<p>Found " . $tiles->count() . " tiles</p>";
    $allAccessible = true;
    foreach ($tiles as $tile) {
        $bleed = json_decode($tile->image_with_bleed, true);
        if (is_array($bleed)) {
            foreach ($bleed as $path) {
                $filename = basename($path);
                $fullPath = __DIR__ . '/storage/designCollageImages/' . $filename;
                $accessible = file_exists($fullPath);
                if (!$accessible) $allAccessible = false;
                
                $status = $accessible ? '✓' : '✗';
                $color = $accessible ? 'green' : 'red';
                echo "<div style='color: {$color};'>{$status} {$filename}</div>";
            }
        }
    }
    
    if ($allAccessible) {
        echo "<p style='color: green; font-weight: bold;'>All files accessible via symlink!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Some files are NOT accessible!</p>";
    }
    ?>
    
    <script>
    function testFetch() {
        const url = '<?= $url ?>';
        const resultDiv = document.getElementById('fetchResult');
        resultDiv.innerHTML = 'Fetching...';
        
        fetch(url)
            .then(response => {
                if (response.ok) {
                    resultDiv.innerHTML = `<span style="color: green;">✓ SUCCESS! Status: ${response.status}</span>`;
                    return response.blob();
                } else {
                    resultDiv.innerHTML = `<span style="color: red;">✗ FAILED! Status: ${response.status} ${response.statusText}</span>`;
                    throw new Error(`HTTP ${response.status}`);
                }
            })
            .then(blob => {
                resultDiv.innerHTML += `<br>Blob size: ${blob.size} bytes`;
            })
            .catch(err => {
                resultDiv.innerHTML = `<span style="color: red;">✗ ERROR: ${err.message}</span>`;
            });
    }
    </script>
</body>
</html>













