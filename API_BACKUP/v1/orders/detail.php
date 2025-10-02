<?php
// Order Detail API - Single order details

// Allowed origins for CORS
$allowed_origins = [
    'https://dentwizard-app.onrender.com',
    'https://dentwizard.lgstore.com',
    'http://localhost:3000',
    'http://localhost:3001',
    'http://localhost:3002'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: " . $allowed_origins[0]);
}

header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token, X-User-Id");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

// Get order ID from URL parameter - can be numeric ID or OrderID string
$order_param = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($order_param)) {
    die(json_encode(array('success' => false, 'message' => 'Order ID required')));
}

// Check if it's a numeric ID or OrderID string
$is_numeric = is_numeric($order_param);

// Get order details - search by either ID or OrderID
if ($is_numeric) {
    $query = "SELECT o.*, 
              (SELECT COUNT(*) FROM OrderItems WHERE OrderRecordID = o.ID) as item_count,
              (SELECT SUM(Price * Quantity) FROM OrderItems WHERE OrderRecordID = o.ID) as calculated_total
              FROM Orders o 
              WHERE o.ID = " . intval($order_param) . " 
              LIMIT 1";
} else {
    $query = "SELECT o.*, 
              (SELECT COUNT(*) FROM OrderItems WHERE OrderRecordID = o.ID) as item_count,
              (SELECT SUM(Price * Quantity) FROM OrderItems WHERE OrderRecordID = o.ID) as calculated_total
              FROM Orders o 
              WHERE o.OrderID = '" . $mysqli->real_escape_string($order_param) . "' 
              LIMIT 1";
}

$result = $mysqli->query($query);

if (!$result || $result->num_rows === 0) {
    die(json_encode(array('success' => false, 'message' => 'Order not found')));
}

$row = $result->fetch_assoc();

// Calculate the total - check different possible columns
$order_total = 0;
if (isset($row['order_total']) && $row['order_total'] > 0) {
    $order_total = floatval($row['order_total']);
} elseif (isset($row['calculated_total']) && $row['calculated_total'] > 0) {
    $order_total = floatval($row['calculated_total']);
} elseif (isset($row['OrderTotal']) && $row['OrderTotal'] > 0) {
    $order_total = floatval($row['OrderTotal']);
}

// Format the date properly
$order_date = $row['OrderDate'];
if ($order_date && $order_date != '0000-00-00 00:00:00') {
    $formatted_date = date('m/d/Y', strtotime($order_date));
} else {
    $formatted_date = date('m/d/Y');
}

// Get order items for this order
$items = array();
$items_query = "SELECT * FROM OrderItems WHERE OrderRecordID = " . $row['ID'];
$items_result = $mysqli->query($items_query);

if ($items_result && $items_result->num_rows > 0) {
    while ($item = $items_result->fetch_assoc()) {
        $items[] = array(
            'product_id' => $item['ItemID'],
            'sku' => isset($item['FormID']) ? $item['FormID'] : '',
            'name' => $item['FormDescription'],
            'quantity' => intval($item['Quantity']),
            'price' => floatval($item['Price']),
            'total' => floatval($item['Price']) * intval($item['Quantity']),
            'size' => isset($item['size_item']) ? $item['size_item'] : '',
            'color' => isset($item['color_item']) ? $item['color_item'] : '',
            'logo' => isset($item['artwork_logo']) ? $item['artwork_logo'] : ''
        );
        
        // If order total was 0, calculate from items
        if ($order_total == 0) {
            $order_total += floatval($item['Price']) * intval($item['Quantity']);
        }
    }
}

// Add shipping cost if available
$shipping_cost = 0;
if (isset($row['shipping_charge']) && $row['shipping_charge'] > 0) {
    $shipping_cost = floatval($row['shipping_charge']);
} elseif (isset($row['ShipCost']) && $row['ShipCost'] > 0) {
    $shipping_cost = floatval($row['ShipCost']);
}

// Calculate subtotal (items only, without shipping or tax)
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['total'];
}

// Get tax
$tax = isset($row['total_sale_tax']) && $row['total_sale_tax'] > 0 ? floatval($row['total_sale_tax']) : 0;

// Format payment method for display
$payment_method = isset($row['PaymentMethod']) ? $row['PaymentMethod'] : 'Credit Card';
if (strtolower($payment_method) === 'budget') {
    $payment_method = 'Budget Balance';
}

// Build the order response
$order = array(
    'id' => $row['ID'],
    'order_id' => $row['OrderID'],
    'date' => $formatted_date,
    'order_date' => $order_date,
    'created_at' => $order_date,
    'status' => strtolower($row['Status']),
    'total' => $order_total,
    'subtotal' => $subtotal,
    'shipping_cost' => $shipping_cost,
    'tax' => $tax,
    'item_count' => intval($row['item_count']),
    'items' => $items,
    'shipping_address' => array(
        'name' => isset($row['ShipToName']) ? $row['ShipToName'] : $row['Name'],
        'address' => $row['Address1'],
        'address2' => isset($row['Address2']) ? $row['Address2'] : '',
        'city' => $row['City'],
        'state' => $row['State'],
        'zip' => $row['Zip'],
        'phone' => isset($row['Phone']) ? $row['Phone'] : ''
    ),
    'tracking_number' => isset($row['TrackingNumber']) ? $row['TrackingNumber'] : null,
    'shipped_date' => isset($row['ShippedDate']) ? $row['ShippedDate'] : null,
    'delivered_date' => isset($row['DeliveredDate']) ? $row['DeliveredDate'] : null,
    'payment_method' => $payment_method,
    'payment_last4' => isset($row['CardLast4']) ? $row['CardLast4'] : null
);

// Return the single order
echo json_encode($order);
$mysqli->close();
?>
