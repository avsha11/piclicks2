<?php
/**
 * Check if GD library is available
 * Access via: http://localhost:8000/check_gd.php
 */

echo "<h1>GD Library Check</h1>";

if (extension_loaded('gd')) {
    echo "<p style='color: green; font-size: 20px;'>✅ GD extension is LOADED and ready to use!</p>";
    
    $gdInfo = gd_info();
    echo "<h2>GD Information:</h2>";
    echo "<table border='1' cellpadding='5'>";
    foreach ($gdInfo as $key => $value) {
        $val = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
        echo "<tr><td><strong>$key</strong></td><td>$val</td></tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>✅ Good News!</h2>";
    echo "<p>GD is available, which means I can modify the preview code to use GD instead of Imagick.</p>";
    echo "<p><strong>Would you like me to do that?</strong></p>";
    
} else {
    echo "<p style='color: red;'>❌ GD extension is NOT loaded</p>";
    echo "<p>This is unusual for XAMPP - GD should be enabled by default.</p>";
}

echo "<hr>";
echo "<p><a href='check_imagick.php'>← Back to Imagick Check</a></p>";

