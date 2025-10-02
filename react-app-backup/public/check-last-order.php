<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = "localhost";
$user = "rwaf";
$pass = "Py*uhb\$L\$##";
$db = "rwaf";

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $mysqli->connect_error]);
    exit;
}

// Get the most recent order
$query = "SELECT * FROM Orders ORDER BY order_id DESC LIMIT 1";
$result = $mysqli->query($query);
$order = $result->fetch_assoc();

if ($order) {
    // Get the order items with their attributes
    $orderItemsQuery = "SELECT * FROM OrderItems WHERE order_id = " . $order['order_id'];
    $itemsResult = $mysqli->query($orderItemsQuery);
    
    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = [
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'size_item' => $item['size_item'],
            'color_item' => $item['color_item'],
            'artwork_logo' => $item['artwork_logo']
        ];
    }
    
    echo json_encode([
        'order' => [
            'order_id' => $order['order_id'],
            'order_number' => $order['order_number'],
            'user_id' => $order['user_id'],
            'order_date' => $order['order_date'],
            'total_amount' => $order['total_amount'],
            'status' => $order['status']
        ],
        'items' => $items
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => 'No orders found']);
}

$mysqli->close();
?>