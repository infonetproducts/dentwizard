<?php
// Check if attributes were saved in OrderItems
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$servername = "localhost";
$username = "rwaf";
$password = "Py*uhb\$L\$##";
$dbname = "rwaf";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die(json_encode(array('error' => 'Connection failed')));
}

// Get the most recent order items
$query = "SELECT 
    oi.OrderRecordID,
    oi.ItemID,
    oi.FormDescription as product_name,
    oi.Quantity,
    oi.Price,
    oi.size_item,
    oi.color_item,
    oi.artwork_logo,
    o.order_id,
    o.OrderDate,
    o.user_id
FROM OrderItems oi
JOIN Orders o ON oi.OrderRecordID = o.id
WHERE o.user_id = 20296
ORDER BY o.OrderDate DESC
LIMIT 5";

$result = mysqli_query($conn, $query);

$items = array();
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = array(
        'order_id' => $row['order_id'],
        'order_date' => $row['OrderDate'],
        'product' => $row['product_name'],
        'quantity' => $row['Quantity'],
        'price' => $row['Price'],
        'size' => $row['size_item'],
        'color' => $row['color_item'],
        'artwork' => $row['artwork_logo']
    );
}

echo json_encode(array(
    'success' => true,
    'recent_order_items' => $items,
    'note' => 'Showing Jamie\'s recent order items with attributes'
), JSON_PRETTY_PRINT);

mysqli_close($conn);
?>