<?php
// Get Cart API - Matching the add-working.php structure

// Start session
session_start();

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get cart items from session
$cart_items = isset($_SESSION['cart_items']) ? $_SESSION['cart_items'] : array();

// Calculate totals
$total_items = 0;
$subtotal = 0;
$unique_items = 0;

if (!empty($cart_items)) {
    foreach ($cart_items as $item) {
        $total_items += $item['quantity'];
        $subtotal += $item['total'];
    }
    $unique_items = count($cart_items);
}

// Calculate tax and shipping
$tax = $subtotal * 0.0825; // 8.25% tax
$shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
$total = $subtotal + $tax + $shipping;

// Return response
echo json_encode(array(
    'success' => true,
    'data' => array(
        'items' => array_values($cart_items),
        'summary' => array(
            'total_items' => $total_items,
            'unique_items' => $unique_items,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2)
        ),
        'budget' => array(
            'has_budget' => false,
            'remaining' => 0
        ),
        'discounts' => array(
            'gift_card' => null,
            'promo_code' => null
        ),
        'session_id' => session_id()
    )
));
?>