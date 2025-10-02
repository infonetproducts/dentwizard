<?php
// Simple Orders Test API - Minimal version for testing
// Exactly matches detail.php pattern but simplified

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// Get user_id parameter - same as create.php
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 19346; // Default for testing

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// Simple query to get orders
$sql = "SELECT ID, OrderID, OrderDate, Status, order_total 
        FROM Orders 
        WHERE UserID = $user_id 
        ORDER BY OrderDate DESC 
        LIMIT 10";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(array('status' => 'error', 'message' => 'Query failed')));
}

$orders = array();

while ($order = $result->fetch_assoc()) {
    $orders[] = array(
        'id' => intval($order['ID']),
        'order_number' => $order['OrderID'],
        'created_at' => $order['OrderDate'],
        'status' => $order['Status'],
        'total' => floatval($order['order_total'])
    );
}

echo json_encode($orders);

$mysqli->close();
?>