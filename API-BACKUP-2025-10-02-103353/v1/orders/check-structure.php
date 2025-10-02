<?php
// Check Orders table structure - Using correct mysqli object style
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection - using your working pattern
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Database connection failed')));
}

$response = array();

// Check Orders table columns
$result = $mysqli->query("SHOW COLUMNS FROM Orders");
if ($result) {
    $columns = array();
    while ($row = $result->fetch_assoc()) {
        $columns[] = array(
            'field' => $row['Field'],
            'type' => $row['Type']
        );
    }
    $response['orders_columns'] = $columns;
}

// Check OrderItems attribute columns
$result2 = $mysqli->query("SHOW COLUMNS FROM OrderItems WHERE Field IN ('size_item', 'color_item', 'artwork_logo')");
if ($result2) {
    $attrs = array();
    while ($row = $result2->fetch_assoc()) {
        $attrs[] = $row['Field'];
    }
    $response['orderitems_attributes'] = $attrs;
}

// Get a sample recent order to see structure
$result3 = $mysqli->query("SELECT * FROM Orders WHERE user_id = 20296 ORDER BY id DESC LIMIT 1");
if ($result3) {
    $order = $result3->fetch_assoc();
    if ($order) {
        $response['sample_order'] = array(
            'id' => $order['id'],
            'order_id' => $order['order_id'],
            'order_total' => $order['order_total'],
            'shipping_charge' => $order['shipping_charge']
        );
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);

$mysqli->close();
?>