<?php
// Cart Add API - ULTRA SIMPLE VERSION
// Works with PHP 5.6+ without prepared statements

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Suppress all errors
@error_reporting(0);
@ini_set('display_errors', 0);

// Start session quietly
@session_start();

// Get input
$input = @json_decode(file_get_contents('php://input'), true);

// Check required fields
if (!$input || !isset($input['product_id'])) {
    die(json_encode(array('success' => false, 'error' => 'Product ID required')));
}

$product_id = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
$size = isset($input['size']) ? $input['size'] : '';
$color = isset($input['color']) ? $input['color'] : '';

// Connect to database - same as working detail.php
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'error' => 'Database error')));
}

// Get product - simple query without prepared statement
$product_id_safe = $mysqli->real_escape_string($product_id);
$query = "SELECT ID, item_title, Price, ImageFile, CID FROM Items WHERE ID = '$product_id_safe' LIMIT 1";
$result = @$mysqli->query($query);

if (!$result || $result->num_rows == 0) {
    die(json_encode(array('success' => false, 'error' => 'Product not found')));
}

$product = $result->fetch_assoc();
$mysqli->close();

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add to cart
$cart_key = $product_id . '_' . $size . '_' . $color;
$price = (float)$product['Price'];

// Check if exists
$found = false;
for ($i = 0; $i < count($_SESSION['cart']); $i++) {
    if (isset($_SESSION['cart'][$i]['key']) && $_SESSION['cart'][$i]['key'] == $cart_key) {
        $_SESSION['cart'][$i]['quantity'] += $quantity;
        $_SESSION['cart'][$i]['total'] = $_SESSION['cart'][$i]['price'] * $_SESSION['cart'][$i]['quantity'];
        $found = true;
        break;
    }
}

// Add new if not found
if (!$found) {
    $_SESSION['cart'][] = array(
        'key' => $cart_key,
        'id' => $cart_key,
        'product_id' => $product_id,
        'name' => $product['item_title'],
        'quantity' => $quantity,
        'price' => $price,
        'total' => $price * $quantity,
        'size' => $size,
        'color' => $color
    );
}

// Calculate totals
$total_items = 0;
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['total'];
}

// Output success
echo json_encode(array(
    'success' => true,
    'message' => 'Added to cart',
    'data' => array(
        'items' => $_SESSION['cart'],
        'summary' => array(
            'total_items' => $total_items,
            'subtotal' => $subtotal
        )
    )
));
?>