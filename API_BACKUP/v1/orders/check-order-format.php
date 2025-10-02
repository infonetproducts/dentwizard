<?php
header('Content-Type: text/plain');

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "=== ORDER ID FORMAT INVESTIGATION ===\n\n";

echo "Checking your example: 0930-095443-17668\n";
echo str_repeat("-", 80) . "\n\n";

// Check if 17668 is a database ID
$result = $mysqli->query("SELECT ID, OrderID, UserID, OrderDate, order_total FROM Orders WHERE ID = 17668");
if ($row = $result->fetch_assoc()) {
    echo "✓ Found order with DATABASE ID = 17668:\n";
    echo "  OrderID: {$row['OrderID']}\n";
    echo "  UserID: {$row['UserID']}\n";
    echo "  Date: {$row['OrderDate']}\n";
    echo "  Total: \${$row['order_total']}\n\n";
    echo "CONCLUSION: The last number (17668) is the DATABASE AUTO-INCREMENT ID\n\n";
} else {
    echo "✗ No order found with database ID = 17668\n\n";
}

// Check if 17668 is a user ID
$result = $mysqli->query("SELECT ID, OrderID, UserID, OrderDate FROM Orders WHERE UserID = 17668 LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "✓ Found order with USER ID = 17668:\n";
    echo "  Database ID: {$row['ID']}\n";
    echo "  OrderID: {$row['OrderID']}\n";
    echo "  Date: {$row['OrderDate']}\n\n";
    echo "CONCLUSION: The last number (17668) is the USER ID\n\n";
} else {
    echo "✗ No orders found for user ID = 17668\n\n";
}

// Show 5 recent orders to see the pattern
echo str_repeat("=", 80) . "\n";
echo "RECENT 5 ORDERS (to see pattern):\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("SELECT ID, OrderID, UserID, OrderDate FROM Orders ORDER BY OrderDate DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo sprintf("DB_ID: %-6d | OrderID: %-25s | UserID: %-6d | Date: %s\n", 
        $row['ID'], $row['OrderID'], $row['UserID'], $row['OrderDate']);
}

// Check Jamie Krugger's recent orders
echo "\n" . str_repeat("=", 80) . "\n";
echo "ORDERS FOR USER 20296 (Jamie Krugger):\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("SELECT ID, OrderID, UserID, OrderDate, order_total, Status 
                         FROM Orders WHERE UserID = 20296 ORDER BY OrderDate DESC LIMIT 10");
$count = 0;
while ($row = $result->fetch_assoc()) {
    echo sprintf("DB_ID: %-6d | OrderID: %-25s | Date: %s | Total: $%-7.2f | Status: %s\n", 
        $row['ID'], $row['OrderID'], $row['OrderDate'], $row['order_total'], $row['Status']);
    $count++;
}

if ($count === 0) {
    echo "NO ORDERS FOUND for user 20296!\n";
}

$mysqli->close();
?>
