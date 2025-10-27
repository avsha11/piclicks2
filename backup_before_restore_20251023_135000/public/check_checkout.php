<?php
/**
 * Debug script to check checkout issues
 * Access via: http://localhost:8000/check_checkout.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "<h1>Checkout Debug</h1>";
echo "<hr>";

// Check if user is logged in
echo "<h2>1. Authentication Check</h2>";
if (Auth::check()) {
    $user = Auth::user();
    echo "<p style='color: green;'>✅ User is logged in: {$user->name} ({$user->email})</p>";
} else {
    echo "<p style='color: red;'>❌ User is NOT logged in</p>";
    echo "<p><strong>You need to be logged in to access checkout!</strong></p>";
}

// Check database connection
echo "<hr>";
echo "<h2>2. Database Connection</h2>";
try {
    DB::connection()->getPdo();
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Database connection: FAILED - " . $e->getMessage() . "</p>";
}

// Check if Countries table exists and has data
echo "<hr>";
echo "<h2>3. Countries Table Check</h2>";
try {
    $countriesCount = DB::table('countries')->count();
    if ($countriesCount > 0) {
        echo "<p style='color: green;'>✅ Countries table has {$countriesCount} countries</p>";
    } else {
        echo "<p style='color: red;'>❌ Countries table is empty</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Error accessing countries table: " . $e->getMessage() . "</p>";
}

// Check PayPal configuration
echo "<hr>";
echo "<h2>4. PayPal Configuration</h2>";
$clientId = env('PAYPAL_CLIENT_ID');
$clientSecret = env('PAYPAL_CLIENT_SECRET');
$baseUrl = env('PAYPAL_API_BASEURL');

if ($clientId && $clientSecret && $baseUrl) {
    echo "<p style='color: green;'>✅ PayPal credentials are configured</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><td>Client ID</td><td>" . substr($clientId, 0, 20) . "...</td></tr>";
    echo "<tr><td>Client Secret</td><td>" . (strlen($clientSecret) > 0 ? 'Set (hidden)' : 'Not set') . "</td></tr>";
    echo "<tr><td>Base URL</td><td>{$baseUrl}</td></tr>";
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ PayPal credentials are NOT configured</p>";
    echo "<p>Missing:</p><ul>";
    if (!$clientId) echo "<li>PAYPAL_CLIENT_ID</li>";
    if (!$clientSecret) echo "<li>PAYPAL_CLIENT_SECRET</li>";
    if (!$baseUrl) echo "<li>PAYPAL_API_BASEURL</li>";
    echo "</ul>";
    echo "<p><em>Note: Checkout might still work but payment will fail</em></p>";
}

// Check cart items
echo "<hr>";
echo "<h2>5. Cart Check</h2>";
if (Auth::check()) {
    try {
        $userId = Auth::id();
        $cartItems = DB::table('design_collage_cart')
            ->where('user_id', $userId)
            ->get();
        
        if ($cartItems->count() > 0) {
            echo "<p style='color: green;'>✅ Cart has {$cartItems->count()} items</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Cart is empty</p>";
        }
    } catch (\Exception $e) {
        echo "<p style='color: red;'>❌ Error checking cart: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>Cannot check cart - user not logged in</p>";
}

// Check recent Laravel logs
echo "<hr>";
echo "<h2>6. Recent Error Logs</h2>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file($logFile);
    $errorLogs = [];
    
    // Get last 50 lines and filter for errors
    $recentLogs = array_slice($logs, -50);
    foreach ($recentLogs as $line) {
        if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false || stripos($line, 'checkout') !== false) {
            $errorLogs[] = $line;
        }
    }
    
    if (count($errorLogs) > 0) {
        echo "<p>Recent errors related to checkout:</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 11px;'>";
        foreach ($errorLogs as $log) {
            echo "<span style='color: red;'>" . htmlspecialchars($log) . "</span>";
        }
        echo "</pre>";
    } else {
        echo "<p style='color: green;'>✅ No recent errors found</p>";
    }
} else {
    echo "<p>No log file found</p>";
}

echo "<hr>";
echo "<h2>Summary & Next Steps</h2>";

if (!Auth::check()) {
    echo "<p style='color: red;'><strong>⚠️ Main Issue: You are not logged in!</strong></p>";
    echo "<p>The checkout page requires authentication. Please:</p>";
    echo "<ol>";
    echo "<li><a href='/'>Go to homepage</a></li>";
    echo "<li>Click 'Login' button</li>";
    echo "<li>Log in with your credentials</li>";
    echo "<li>Try accessing checkout again</li>";
    echo "</ol>";
} else {
    echo "<p style='color: green;'>✅ You are logged in</p>";
    echo "<p>If checkout still doesn't work, check the error logs above for specific issues.</p>";
    echo "<p><a href='/checkout'>→ Try Checkout Page</a></p>";
}

