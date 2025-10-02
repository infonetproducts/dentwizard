<?php
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "Recent Orders:\n\n";
$result = $mysqli->query("SELECT FormID as OrderID, UserID, OrderDate, order_total, Status FROM Orders ORDER BY OrderDate DESC LIMIT 10");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Order: {$row['OrderID']} | User: {$row['UserID']} | Date: {$row['OrderDate']} | Total: \${$row['order_total']} | Status: {$row['Status']}\n";
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}

echo "\n\nChecking table structure:\n";
$result = $mysqli->query("DESCRIBE Orders");
while ($row = $result->fetch_assoc()) {
    echo "{$row['Field']} - {$row['Type']} - {$row['Key']}\n";
}

$mysqli->close();
?>
