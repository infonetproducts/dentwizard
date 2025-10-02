<?php
// Cart Remove Item API - PHP 5.6 Compatible

// CORS Headers - MUST be at the very top
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session for cart
if (session_id() === '') {
    session_start();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input || !isset($input['item_id'])) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Item ID is required'
    ));
    exit;
}

$item_id = $input['item_id'];

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Find and remove item
$item_found = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $item_id) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
        $item_found = true;
        break;
    }
}

if (!$item_found) {
    http_response_code(404);
    echo json_encode(array(
        'success' => false,
        'error' => 'Item not found in cart'
    ));
    exit;
}

// Calculate new cart totals
$cart_total = 0;
$total_items = 0;
$unique_items = count($_SESSION['cart']);

foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['total'];
    $total_items += $item['quantity'];
}

// Calculate tax and shipping
$tax_rate = 0.0825;
$tax = $cart_total * $tax_rate;
$shipping = $cart_total > 100 ? 0 : 10;
$total = $cart_total + $tax + $shipping;

// Return response
$response = array(
    'success' => true,
    'message' => 'Item removed from cart',
    'data' => array(
        'items' => array_values($_SESSION['cart']),
        'summary' => array(
            'total_items' => $total_items,
            'unique_items' => $unique_items,
            'subtotal' => round($cart_total, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2)
        )
    )
);

echo json_encode($response);
?>