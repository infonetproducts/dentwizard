<?php
// Direct test - returns Joe's orders without any auth check
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

// Get Joe's orders directly
$sql = "SELECT 
    OrderID as id,
    OrderDate as createdAt,
    Status as status,
    order_total as total
FROM Orders 
WHERE UserID = 19346 
ORDER BY OrderDate DESC";

$result = $mysqli->query($sql);
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);
$mysqli->close();
?>