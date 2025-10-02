<?php
// Check if Jamie's order was created
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
$query = "SELECT * FROM mtsOrders WHERE UserID = $user_id ORDER BY DateAdded DESC LIMIT 5";
$result = $mysqli->query($query);

$orders = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = array(
            'order_id' => $row['OrderID'],
            'date' => $row['DateAdded'],
            'total' => $row['OrderTotal'],
            'status' => $row['Status']
        );
    }
}

// Also check if the tables exist
$tables_query = "SHOW TABLES LIKE '%Order%'";
$tables_result = $mysqli->query($tables_query);
$tables = array();
if ($tables_result) {
    while ($row = $tables_result->fetch_array()) {
        $tables[] = $row[0];
    }
}

die(json_encode(array(
    'success' => true,
    'user_id' => $user_id,
    'orders_count' => count($orders),
    'recent_orders' => $orders,
    'order_tables' => $tables
)));
?>