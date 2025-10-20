<?php
/**
 * Debug script to check why checkout is failing
 * Access via: http://localhost:8000/debug_checkout.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Illuminate\Support\Facades\DB;

echo "<h1>Checkout Debug</h1>";
echo "<hr>";

// Check if user is logged in
echo "<h2>1. Authentication Check</h2>";
if (auth()->check()) {
    echo "<p style='color: green;'>✅ User is logged in: " . auth()->user()->email . "</p>";
} else {
    echo "<p style='color: red;'>❌ User is NOT logged in</p>";
    echo "<p><strong>You must be logged in to access checkout</strong></p>";
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

// Check countries table
echo "<hr>";
echo "<h2>3. Countries Table</h2>";
try {
    $countries = DB::table('countries')->count();
    echo "<p style='color: green;'>✅ Countries table: Found {$countries} countries</p>";
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Countries table error: " . $e->getMessage() . "</p>";
}

// Check PayPal configuration
echo "<hr>";
echo "<h2>4. PayPal Configuration</h2>";
$paypalClientId = env('PAYPAL_CLIENT_ID');
$paypalSecret = env('PAYPAL_CLIENT_SECRET');
$paypalBaseUrl = env('PAYPAL_API_BASEURL');

$paypalConfigured = !empty($paypalClientId) && !empty($paypalSecret) && !empty($paypalBaseUrl);

if ($paypalConfigured) {
    echo "<p style='color: green;'>✅ PayPal credentials are configured</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><td><strong>Client ID</strong></td><td>" . substr($paypalClientId, 0, 10) . "...</td></tr>";
    echo "<tr><td><strong>Secret</strong></td><td>" . (empty($paypalSecret) ? '❌ Missing' : '✅ Set') . "</td></tr>";
    echo "<tr><td><strong>Base URL</strong></td><td>" . $paypalBaseUrl . "</td></tr>";
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ PayPal credentials are NOT fully configured</p>";
    echo "<p>Missing:</p>";
    echo "<ul>";
    if (empty($paypalClientId)) echo "<li>PAYPAL_CLIENT_ID</li>";
    if (empty($paypalSecret)) echo "<li>PAYPAL_CLIENT_SECRET</li>";
    if (empty($paypalBaseUrl)) echo "<li>PAYPAL_API_BASEURL</li>";
    echo "</ul>";
    echo "<p><strong>Note:</strong> Checkout may still work, but PayPal payment will fail.</p>";
}

// Check cart items
echo "<hr>";
echo "<h2>5. Cart Items</h2>";
if (auth()->check()) {
    $userId = auth()->id();
    $cartItems = DB::table('carts')->where('user_id', $userId)->get();
    
    if ($cartItems->count() > 0) {
        echo "<p style='color: green;'>✅ Found {$cartItems->count()} items in cart</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Product ID</th><th>Quantity</th><th>Price</th></tr>";
        foreach ($cartItems as $item) {
            echo "<tr>";
            echo "<td>{$item->id}</td>";
            echo "<td>{$item->product_id}</td>";
            echo "<td>{$item->quantity}</td>";
            echo "<td>\${$item->price}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ Cart is empty</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Cannot check cart - user not logged in</p>";
}

// Test generatePaypalAccessToken function
echo "<hr>";
echo "<h2>6. Test PayPal Access Token Generation</h2>";
try {
    $accessToken = generatePaypalAccessToken();
    if ($accessToken) {
        echo "<p style='color: green;'>✅ PayPal access token generated successfully</p>";
        echo "<p><small>Token: " . substr($accessToken, 0, 20) . "...</small></p>";
    } else {
        echo "<p style='color: orange;'>⚠️ PayPal access token is NULL (PayPal not configured)</p>";
        echo "<p>This is OK if you're not using PayPal yet.</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Error generating PayPal token: " . $e->getMessage() . "</p>";
}

// Check Laravel logs
echo "<hr>";
echo "<h2>7. Recent Laravel Logs</h2>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -30); // Last 30 lines
    
    echo "<p>Last 30 lines from laravel.log:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 11px;'>";
    foreach ($recentLogs as $line) {
        if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
            echo "<span style='color: red;'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p>No log file found at: {$logFile}</p>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>Possible issues:</strong></p>";
echo "<ol>";
if (!auth()->check()) {
    echo "<li style='color: red;'><strong>USER NOT LOGGED IN</strong> - Must log in first</li>";
}
if (!$paypalConfigured) {
    echo "<li style='color: orange;'>PayPal not configured (may cause issues if trying to use PayPal)</li>";
}
echo "<li>Check the Laravel logs above for specific error messages</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='check_imagick.php'>→ Check Image Processing</a></p>";
if (auth()->check()) {
    echo "<p><a href='checkout'>→ Try Checkout Again</a></p>";
} else {
    echo "<p>Please log in first, then try checkout</p>";
}

