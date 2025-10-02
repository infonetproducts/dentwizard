<?php
// Cart Get API - SIMPLE VERSION for PHP 5.6

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Start output buffering
ob_start();

// Disable error output
error_reporting(0);
ini_set('display_errors', 0);

// Start session
@session_start();

// Get cart items
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

// Calculate totals
$total_items = 0;
$subtotal = 0;

foreach ($cart_items as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['total'];
}

// Calculate tax and shipping
$tax = $subtotal * 0.0825;
$shipping = $subtotal > 100 ? 0 : 10;
$total = $subtotal + $tax + $shipping;

// Clear buffer and output
ob_clean();

// Return response
echo json_encode(array(
    'success' => true,
    'data' => array(
        'items' => array_values($cart_items),
        'summary' => array(
            'total_items' => $total_items,
            'unique_items' => count($cart_items),
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2)
        ),
        'budget' => array(
            'has_budget' => false
        ),
        'session_id' => session_id()
    )
));
?>