<?php
// Cart Get API - PHP 5.6 Compatible
// Fixed version using direct database connection like working detail.php

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

// Get cart from session
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

// Calculate cart totals
$cart_total = 0;
$total_items = 0;
$unique_items = count($cart_items);

foreach ($cart_items as $item) {
    $cart_total += $item['total'];
    $total_items += $item['quantity'];
}

// Calculate tax and shipping
$tax_rate = 0.0825; // 8.25% tax rate
$tax = $cart_total * $tax_rate;
$shipping = $cart_total > 100 ? 0 : 10; // Free shipping over $100
$total = $cart_total + $tax + $shipping;

// Return response
$response = array(
    'success' => true,
    'data' => array(
        'items' => array_values($cart_items),
        'summary' => array(
            'total_items' => $total_items,
            'unique_items' => $unique_items,
            'subtotal' => round($cart_total, 2),
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
);

echo json_encode($response);
?>