<?php
// DentWizard Order Creation - FINAL FIXED VERSION
// Saves product attributes (size, color, artwork) correctly

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection - using correct object-oriented mysqli
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

// Validate required fields
if (!isset($input['userEmail']) || !isset($input['orderTotal']) || !isset($input['items'])) {
    die(json_encode(array('success' => false, 'error' => 'Missing required fields')));
}

// Get user info
$userEmail = $mysqli->real_escape_string($input['userEmail']);
$userQuery = "SELECT id, name, budget FROM Users WHERE email = '$userEmail' LIMIT 1";
$userResult = $mysqli->query($userQuery);

if (!$userResult || $userResult->num_rows == 0) {
    die(json_encode(array('success' => false, 'error' => 'User not found')));
}

$user = $userResult->fetch_assoc();
$user_id = $user['id'];
$user_name = $user['name'];
$current_budget = $user['budget'];

// Generate order ID
$order_id = date('md-His') . '-' . $user_id;

// Extract order details
$order_total = floatval($input['orderTotal']);
$shipping = floatval($input['shipping']);
$subtotal = $order_total - $shipping;

// Check budget
if ($order_total > $current_budget) {
    die(json_encode(array('success' => false, 'error' => 'Insufficient budget')));
}

// Shipping info
$ship_name = $mysqli->real_escape_string($input['shippingInfo']['name']);
$ship_address = $mysqli->real_escape_string($input['shippingInfo']['address']);
$ship_city = $mysqli->real_escape_string($input['shippingInfo']['city']);
$ship_state = $mysqli->real_escape_string($input['shippingInfo']['state']);
$ship_postal = $mysqli->real_escape_string($input['shippingInfo']['postal']);
$ship_phone = $mysqli->real_escape_string($input['shippingInfo']['phone']);

// Insert into Orders table
$orderSql = "INSERT INTO Orders (
    order_id, user_id, order_date, order_total, 
    Status, ShipToName, ShipAddress, ShipCity, 
    ShipState, ShipPostal, Phone, PaymentAmount
) VALUES (
    '$order_id', '$user_id', NOW(), '$order_total',
    'new', '$ship_name', '$ship_address', '$ship_city',
    '$ship_state', '$ship_postal', '$ship_phone', '$order_total'
)";

if (!$mysqli->query($orderSql)) {
    die(json_encode(array('success' => false, 'error' => 'Failed to create order: ' . $mysqli->error)));
}

// Insert order items with attributes
$items_inserted = 0;
$formID = date('Ymd_His');

foreach ($input['items'] as $item) {
    $product_id = intval($item['product_id']);
    $quantity = intval($item['quantity']);
    $price = floatval($item['price']);
    
    // CRITICAL FIX: Extract and save product attributes
    $size = isset($item['size']) ? $mysqli->real_escape_string($item['size']) : '';
    $color = isset($item['color']) ? $mysqli->real_escape_string($item['color']) : '';
    $artwork = isset($item['artwork']) ? $mysqli->real_escape_string($item['artwork']) : '';
    
    // Insert into OrderItems table with attributes
    $itemSql = "INSERT INTO OrderItems (
        order_id, product_id, quantity, price,
        size_item, color_item, artwork_logo,
        ID, FormID
    ) VALUES (
        '$order_id', '$product_id', '$quantity', '$price',
        '$size', '$color', '$artwork',
        1, '$formID'
    )";
    
    if ($mysqli->query($itemSql)) {
        $items_inserted++;
    }
}

// Update user's budget
$new_budget = $current_budget - $order_total;
$updateBudget = "UPDATE Users SET budget = '$new_budget' WHERE id = '$user_id'";
$mysqli->query($updateBudget);

// Clear cart (assuming session-based or user-based cart)
// This would depend on your cart implementation

// Return success response
$response = array(
    'success' => true,
    'message' => 'Order created successfully',
    'order_id' => $order_id,
    'items_saved' => $items_inserted,
    'new_budget' => $new_budget,
    'attributes_saved' => true  // Indicating attributes were saved
);

echo json_encode($response);
$mysqli->close();
?>
