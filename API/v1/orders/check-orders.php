<?php
// Check if Jamie's order was created - FIXED WITH CORRECT TABLE NAME
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Database connection failed')));
}

// Check for recent orders from Jamie (user ID 20296)
$user_id = 20296;
$query = "SELECT * FROM Orders WHERE UserID = $user_id ORDER BY OrderDate DESC LIMIT 5";
$result = $mysqli->query($query);

$orders = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = array(
            'order_id' => $row['OrderID'],
            'date' => $row['OrderDate'],
            'total' => isset($row['order_total']) ? $row['order_total'] : 0,
            'status' => $row['Status']
        );
    }
}

// Also check user's updated budget
$user_query = "SELECT Budget FROM Users WHERE ID = $user_id";
$user_result = $mysqli->query($user_query);
$current_budget = null;
if ($user_result && $user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $current_budget = $user['Budget'];
}

die(json_encode(array(
    'success' => true,
    'user_id' => $user_id,
    'orders_count' => count($orders),
    'recent_orders' => $orders,
    'current_budget' => $current_budget,
    'original_budget' => 500
)));
?>