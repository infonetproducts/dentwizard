<?php
// Test PHP and Database - Using correct mysqli object style
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/plain");

echo "PHP is working\n";
echo "PHP Version: " . phpversion() . "\n\n";

// Database connection - using same pattern as your working files
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    echo "Database connection failed: " . $mysqli->connect_error . "\n";
} else {
    echo "Database connected successfully!\n\n";
    
    // Check Orders table
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM Orders");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Orders table has " . $row['cnt'] . " records\n";
    } else {
        echo "Error checking Orders: " . $mysqli->error . "\n";
    }
    
    // Check OrderItems table
    $result2 = $mysqli->query("SELECT COUNT(*) as cnt FROM OrderItems");
    if ($result2) {
        $row2 = $result2->fetch_assoc();
        echo "OrderItems table has " . $row2['cnt'] . " records\n";
    } else {
        echo "Error checking OrderItems: " . $mysqli->error . "\n";
    }
    
    $mysqli->close();
}

echo "\nTest complete.";
?>