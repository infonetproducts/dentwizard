<?php
// Quick test to check if logo data is in the database
header("Content-Type: application/json");

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Database connection failed')));
}

// Get the most recent order and its items
$query = "SELECT 
    o.ID as order_id,
    o.OrderID as order_number,
    o.OrderDate,
    oi.FormDescription as product_name,
    oi.size_item,
    oi.color_item,
    oi.artwork_logo
FROM Orders o
LEFT JOIN OrderItems oi ON o.ID = oi.OrderRecordID
ORDER BY o.OrderDate DESC
LIMIT 5";

$result = $mysqli->query($query);

$items = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

echo json_encode(array(
    'success' => true,
    'recent_orders' => $items
), JSON_PRETTY_PRINT);

$mysqli->close();
?>
