<?php
// Minimal Add to Cart - Build up step by step
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

// Check if we have input
if (!$input || !isset($input['product_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Product ID required'));
    exit();
}

// Get basic values - NO database yet
$product_id = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
$size = isset($input['size']) ? $input['size'] : '';
$color = isset($input['color']) ? $input['color'] : '';

// Initialize cart
if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

// Create simple cart item WITHOUT database
$cart_item = array(
    'id' => $product_id . '_' . $size . '_' . $color,
    'product_id' => $product_id,
    'name' => 'Test Product Name',
    'quantity' => $quantity,
    'price' => 65.00,
    'total' => 65.00 * $quantity,
    'size' => $size,
    'color' => $color
);

// Add to cart
$_SESSION['cart_items'][] = $cart_item;

// Return success WITHOUT complex calculations
echo json_encode(array(
    'success' => true,
    'message' => 'Added to cart (test version)',
    'session_id' => session_id(),
    'cart_count' => count($_SESSION['cart_items'])
));
?>