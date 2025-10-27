<?php
/**
 * Test script to debug homepage 500 error
 * Access via: http://localhost:8000/test_homepage.php
 */

echo "<h1>Homepage Debug Test</h1>";
echo "<hr>";

// Test 1: PHP is working
echo "<p style='color: green;'>✅ PHP is working!</p>";

// Test 2: Can we load Laravel?
echo "<h2>Loading Laravel...</h2>";

try {
    require __DIR__.'/../vendor/autoload.php';
    echo "<p style='color: green;'>✅ Autoloader loaded</p>";
    
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "<p style='color: green;'>✅ App bootstrapped</p>";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<p style='color: green;'>✅ Kernel created</p>";
    
    // Test 3: Can we connect to database?
    echo "<h2>Testing Database...</h2>";
    $app->make('db')->connection()->getPdo();
    echo "<p style='color: green;'>✅ Database connection works!</p>";
    
    // Test 4: Check .env file
    echo "<h2>Environment Check...</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><td>APP_NAME</td><td>" . env('APP_NAME') . "</td></tr>";
    echo "<tr><td>APP_ENV</td><td>" . env('APP_ENV') . "</td></tr>";
    echo "<tr><td>APP_DEBUG</td><td>" . (env('APP_DEBUG') ? 'true' : 'false') . "</td></tr>";
    echo "<tr><td>DB_DATABASE</td><td>" . env('DB_DATABASE') . "</td></tr>";
    echo "</table>";
    
    // Test 5: Try to call the actual homepage controller
    echo "<h2>Testing Homepage Controller...</h2>";
    
    $request = Illuminate\Http\Request::create('/', 'GET');
    
    try {
        $response = $kernel->handle($request);
        echo "<p style='color: green;'>✅ Homepage controller executed successfully!</p>";
        echo "<p>Status Code: " . $response->getStatusCode() . "</p>";
        
        if ($response->getStatusCode() == 500) {
            echo "<p style='color: red;'>❌ Controller returned 500 error</p>";
            echo "<p>Check the content below for error details:</p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 400px; overflow: auto;'>";
            echo htmlspecialchars($response->getContent());
            echo "</pre>";
        }
        
    } catch (\Exception $e) {
        echo "<p style='color: red;'>❌ Error calling homepage:</p>";
        echo "<pre style='background: #ffeeee; padding: 10px;'>";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n\n";
        echo "Stack Trace:\n" . $e->getTraceAsString();
        echo "</pre>";
    }
    
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Fatal Error:</p>";
    echo "<pre style='background: #ffeeee; padding: 10px;'>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

echo "<hr>";
echo "<p><a href='/'>→ Try Homepage</a></p>";

