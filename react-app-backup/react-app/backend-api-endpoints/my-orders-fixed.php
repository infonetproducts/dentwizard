<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id, X-Session-ID');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

$mysqli->set_charset("utf8");

// Get user ID from multiple possible sources
$userId = null;

// 1. Try to get from headers (sent by React app)
if (isset($_SERVER['HTTP_X_USER_ID'])) {
    $userId = intval($_SERVER['HTTP_X_USER_ID']);
}

// 2. Try to get from POST body (for compatibility)
if (!$userId) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (isset($data['user_id'])) {
        $userId = intval($data['user_id']);
    } elseif (isset($data['email'])) {
        // Get user ID from email
        $email = $mysqli->real_escape_string($data['email']);
        $userQuery = "SELECT ID FROM Users WHERE Email = '$email'";
        $userResult = $mysqli->query($userQuery);
        
        if ($userResult && $userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $userId = intval($user['ID']);
        }
    }
}

// 3. Try to get from query parameters (for GET requests)
if (!$userId && isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
}

if (!$userId) {
    echo json_encode(array('success' => false, 'message' => 'User identification required'));
    $mysqli->close();
    exit;
}

// Get orders for this user
$ordersQuery = "SELECT 
    OrderID, UserID, order_total, OrderDate, Status,
    ShipToName, Address1, Address2,
    City, State, Zip, country,
    total_sale_tax, shipping_charge
    FROM Orders 
    WHERE UserID = $userId 
    ORDER BY OrderDate DESC";

$ordersResult = $mysqli->query($ordersQuery);

if (!$ordersResult) {
    echo json_encode(array('success' => false, 'message' => 'Failed to fetch orders: ' . $mysqli->error));
    $mysqli->close();
    exit;
}

$orders = array();
while ($order = $ordersResult->fetch_assoc()) {
    $orderId = $order['OrderID'];
    
    // Get order items with attributes
    $itemsQuery = "SELECT 
        ItemID, FormDescription, Quantity, Price,
        size_item, color_item, artwork_logo
        FROM OrderItems 
        WHERE FormID = '" . $mysqli->real_escape_string($order['OrderID']) . "'";
    
    $itemsResult = $mysqli->query($itemsQuery);
    
    $items = array();
    if ($itemsResult) {
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = array(
                'id' => $item['ItemID'],
                'product_id' => $item['ItemID'],
                'name' => $item['FormDescription'],
                'product_name' => $item['FormDescription'],
                'quantity' => intval($item['Quantity']),
                'price' => floatval($item['Price']),
                'size' => $item['size_item'] ?: '',
                'color' => $item['color_item'] ?: '',
                'artwork' => $item['artwork_logo'] ?: ''
            );
        }
    }
    
    $orders[] = array(
        'id' => $order['OrderID'],
        'order_id' => $order['OrderID'],
        'order_number' => $order['OrderID'],
        'date' => $order['OrderDate'],
        'order_date' => $order['OrderDate'],
        'total' => floatval($order['order_total']),
        'total_amount' => floatval($order['order_total']),
        'status' => strtolower($order['Status']),
        'shipping' => array(
            'name' => $order['ShipToName'] ?: '',
            'address1' => $order['Address1'] ?: '',
            'address2' => $order['Address2'] ?: '',
            'city' => $order['City'] ?: '',
            'state' => $order['State'] ?: '',
            'zip' => $order['Zip'] ?: '',
            'country' => $order['country'] ?: ''
        ),
        'paymentAmount' => floatval($order['order_total']),
        'tax' => floatval($order['total_sale_tax']),
        'shippingCost' => floatval($order['shipping_charge']),
        'items' => $items
    );
}

echo json_encode(array('success' => true, 'orders' => $orders, 'count' => count($orders)));
$mysqli->close();
?>
