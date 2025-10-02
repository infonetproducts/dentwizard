<?php
// Simple test to verify orders for user 19346
header('Content-Type: application/json');

// Database connection
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

// Check orders for Joe (ID: 19346)
$user_id = 19346;
$sql = "SELECT OrderID, OrderDate, Total, OrderStatus FROM Orders WHERE UID = $user_id LIMIT 5";
$result = $mysqli->query($sql);

$orders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

echo json_encode([
    'user_id' => $user_id,
    'order_count' => count($orders),
    'sample_orders' => $orders
]);

$mysqli->close();
?>