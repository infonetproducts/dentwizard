<?php
// DentWizard My Orders API - FINAL FIXED VERSION  
// Retrieves orders with product attributes

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die(json_encode(array('success' => false, 'error' => 'Database connection failed')));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Get user email
$userEmail = isset($input['userEmail']) ? $mysqli->real_escape_string($input['userEmail']) : '';

if (empty($userEmail)) {
    die(json_encode(array('success' => false, 'message' => 'User email required')));
}

// Get user ID from email
$userQuery = "SELECT id FROM Users WHERE email = '$userEmail' LIMIT 1";
$userResult = $mysqli->query($userQuery);

if (!$userResult || $userResult->num_rows == 0) {
    die(json_encode(array('success' => false, 'message' => 'User not found')));
}

$user = $userResult->fetch_assoc();
$user_id = $user['id'];

// Get orders for this user
$ordersSql = "SELECT 
    order_id, 
    order_date, 
    order_total,
    Status,
    PaymentAmount,
    ShipToName,
    ShipAddress,
    ShipCity,
    ShipState,
    ShipPostal
FROM Orders 
WHERE user_id = '$user_id' 
ORDER BY order_date DESC 
LIMIT 50";

$ordersResult = $mysqli->query($ordersSql);

if (!$ordersResult) {
    die(json_encode(array('success' => false, 'error' => 'Failed to fetch orders')));
}

$orders = array();

while ($order = $ordersResult->fetch_assoc()) {
    // Get items for this order with attributes
    $itemsSql = "SELECT 
        oi.product_id,
        oi.quantity,
        oi.price,
        oi.size_item,
        oi.color_item,
        oi.artwork_logo,
        p.product_name
    FROM OrderItems oi
    LEFT JOIN Products p ON oi.product_id = p.id
    WHERE oi.order_id = '" . $order['order_id'] . "'";
    
    $itemsResult = $mysqli->query($itemsSql);
    $items = array();
    
    if ($itemsResult) {
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = array(
                'product_id' => $item['product_id'],
                'name' => $item['product_name'] ? $item['product_name'] : 'Product #' . $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                // CRITICAL: Return the attributes
                'size' => $item['size_item'] ? $item['size_item'] : null,
                'color' => $item['color_item'] ? $item['color_item'] : null,
                'artwork' => $item['artwork_logo'] ? $item['artwork_logo'] : null
            );
        }
    }
    
    // Calculate shipping (assuming $10 flat rate if not stored)
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $shipping = $order['order_total'] - $subtotal;
    if ($shipping < 0) $shipping = 10;
    
    // Map status
    $status = strtolower($order['Status']);
    if ($status == 'new') {
        $displayStatus = 'New';
    } elseif ($status == 'inprocess' || $status == 'in process') {
        $displayStatus = 'In Process';
    } elseif ($status == 'cancelled') {
        $displayStatus = 'Cancelled';
    } else {
        $displayStatus = ucfirst($status);
    }
    
    $orders[] = array(
        'order_id' => $order['order_id'],
        'order_date' => date('M d, Y', strtotime($order['order_date'])),
        'total' => number_format($order['order_total'], 2),
        'status' => $displayStatus,
        'shipping' => number_format($shipping, 2),
        'subtotal' => number_format($subtotal, 2),
        'payment_amount' => number_format($order['PaymentAmount'], 2),
        'shipping_info' => array(
            'name' => $order['ShipToName'],
            'address' => $order['ShipAddress'],
            'city' => $order['ShipCity'],
            'state' => $order['ShipState'],
            'postal' => $order['ShipPostal']
        ),
        'items' => $items
    );
}

// Return orders
echo json_encode(array(
    'success' => true,
    'orders' => $orders,
    'count' => count($orders)
));

$mysqli->close();
?>