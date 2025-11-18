<?php
/**
 * Test database connection
 * Access via: http://localhost:8000/test_db.php
 */

echo "<h1>Database Connection Test</h1>";
echo "<hr>";

// Database credentials from .env
$host = '127.0.0.1';
$port = 3306;
$database = 'sanshaco_piclicks_live';
$username = 'root';
$password = '';

echo "<h2>Attempting to connect...</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><td>Host</td><td>$host:$port</td></tr>";
echo "<tr><td>Database</td><td>$database</td></tr>";
echo "<tr><td>Username</td><td>$username</td></tr>";
echo "<tr><td>Password</td><td>" . (empty($password) ? '(empty)' : '(set)') . "</td></tr>";
echo "</table>";

echo "<hr>";

try {
    // Try to connect with mysqli
    $mysqli = new mysqli($host, $username, $password, '', $port);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "<p style='color: green;'>✅ MySQL connection successful!</p>";
    
    // Check if database exists
    $result = $mysqli->query("SHOW DATABASES LIKE '$database'");
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Database '$database' exists!</p>";
        
        // Try to select the database
        if ($mysqli->select_db($database)) {
            echo "<p style='color: green;'>✅ Successfully selected database!</p>";
            
            // List some tables
            echo "<h3>Tables in database:</h3>";
            $tables_result = $mysqli->query("SHOW TABLES");
            
            if ($tables_result) {
                echo "<ul>";
                $count = 0;
                while ($row = $tables_result->fetch_array()) {
                    echo "<li>" . $row[0] . "</li>";
                    $count++;
                }
                echo "</ul>";
                echo "<p>Total tables: $count</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Could not select database: " . $mysqli->error . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Database '$database' does NOT exist!</p>";
        
        echo "<h3>Available databases:</h3>";
        $dbs_result = $mysqli->query("SHOW DATABASES");
        echo "<ul>";
        while ($row = $dbs_result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
        
        echo "<hr>";
        echo "<h2>🔧 How to Fix:</h2>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Click 'New' to create a database</li>";
        echo "<li>Name it: <strong>$database</strong></li>";
        echo "<li>Import the SQL file: <code>sanshaco_piclicks_live_database_17092025.sql</code></li>";
        echo "</ol>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    
    echo "<hr>";
    echo "<h2>🔧 Common Issues:</h2>";
    echo "<ul>";
    echo "<li><strong>MySQL not running</strong> - Start MySQL in XAMPP Control Panel</li>";
    echo "<li><strong>Wrong password</strong> - Check if MySQL has a password set</li>";
    echo "<li><strong>Wrong port</strong> - Make sure MySQL is running on port 3306</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='/'>→ Try Homepage</a></p>";
echo "<p><a href='test_homepage.php'>→ Run Homepage Test Again</a></p>";

