<?php
/**
 * Quick diagnostic script to check Imagick installation
 * Access via: http://localhost:8000/check_imagick.php
 */

echo "<h1>Imagick Extension Check</h1>";

// Check if Imagick is loaded
if (extension_loaded('imagick')) {
    echo "<p style='color: green;'>✅ Imagick extension is LOADED</p>";
    
    // Get Imagick version
    $imagick = new Imagick();
    $version = $imagick->getVersion();
    echo "<p>Version: " . $version['versionString'] . "</p>";
    
    // List supported formats
    echo "<h3>Supported Image Formats:</h3>";
    $formats = Imagick::queryFormats();
    echo "<p>" . implode(', ', $formats) . "</p>";
    
} else {
    echo "<p style='color: red;'>❌ Imagick extension is NOT loaded</p>";
    echo "<p><strong>To install Imagick:</strong></p>";
    echo "<ul>";
    echo "<li>On Ubuntu/Debian: <code>sudo apt-get install php-imagick</code></li>";
    echo "<li>On Windows with XAMPP: Download php_imagick.dll and enable in php.ini</li>";
    echo "<li>On MacOS: <code>pecl install imagick</code></li>";
    echo "</ul>";
    echo "<p>After installation, restart your web server.</p>";
}

echo "<hr>";
echo "<h2>GD Library Check</h2>";

// Check if GD is loaded (fallback option)
if (extension_loaded('gd')) {
    echo "<p style='color: green;'>✅ GD extension is LOADED</p>";
    $gdInfo = gd_info();
    echo "<pre>";
    print_r($gdInfo);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ GD extension is NOT loaded</p>";
}

echo "<hr>";
echo "<h2>Storage Paths Check</h2>";

// Check storage paths
$paths = [
    'Storage Path' => realpath(__DIR__ . '/../storage/app/public/'),
    'Temp Directory' => realpath(__DIR__ . '/../storage/app/public/temp') ?: __DIR__ . '/../storage/app/public/temp',
    'Public Path' => __DIR__,
    'Preview Image 1' => __DIR__ . '/assets/images/preview_livingroom.png',
    'Preview Image 2' => __DIR__ . '/assets/images/preview_kitchen.png',
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Path Type</th><th>Path</th><th>Exists?</th><th>Writable?</th></tr>";
foreach ($paths as $label => $path) {
    $exists = file_exists($path);
    $writable = $exists ? is_writable($path) : 'N/A';
    $existsText = $exists ? '✅ Yes' : '❌ No';
    $writableText = $writable === true ? '✅ Yes' : ($writable === false ? '❌ No' : 'N/A');
    
    echo "<tr>";
    echo "<td><strong>$label</strong></td>";
    echo "<td><code>$path</code></td>";
    echo "<td>$existsText</td>";
    echo "<td>$writableText</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2>PHP Information</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Memory Limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>Max Execution Time</td><td>" . ini_get('max_execution_time') . " seconds</td></tr>";
echo "<tr><td>Upload Max Filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>Post Max Size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<p><em>Script location: " . __FILE__ . "</em></p>";
echo "<p><a href='debug_preview.php?id=853815965'>→ Go to Preview Debug</a></p>";

