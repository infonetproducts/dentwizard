<?php
// Add to Cart - With Database but minimal logic
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Product ID required'));
    exit();
}

$product_id = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
$size = isset($input['size']) ? $input['size'] : '';
$color = isset($input['color']) ? $input['color'] : '';

// Connect to database
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    echo json_encode(array('success' => false, 'error' => 'Database connection failed'));
    exit();
}

// Get product - simple query
$query = "SELECT ID, item_title, Price FROM Items WHERE ID = $product_id LIMIT 1";
$result = $mysqli->query($query);

if (!$result || $result->num_rows == 0) {
    echo json_encode(array('success' => false, 'error' => 'Product not found'));
    exit();
}

$product = $result->fetch_assoc();
$mysqli->close();

// Initialize cart
if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

// Add simple item
$cart_item = array(
    'id' => $product_id . '_' . $size . '_' . $color,
    'product_id' => $product_id,
    'name' => $product['item_title'],
    'quantity' => $quantity,
    'price' => (float)$product['Price'],
    'total' => (float)$product['Price'] * $quantity,
    'size' => $size,
    'color' => $color
);

$_SESSION['cart_items'][] = $cart_item;

// Simple response
echo json_encode(array(
    'success' => true,
    'message' => 'Added to cart',
    'items_in_cart' => count($_SESSION['cart_items'])
));
?>