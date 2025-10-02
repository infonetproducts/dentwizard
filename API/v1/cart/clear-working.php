<?php
// Clear Cart - Working Version

session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Clear the cart
$_SESSION['cart_items'] = array();
$_SESSION['Order'] = array();

// Return empty cart response
echo json_encode(array(
    'success' => true,
    'message' => 'Cart cleared',
    'data' => array(
        'items' => array(),
        'summary' => array(
            'total_items' => 0,
            'unique_items' => 0,
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'total' => 0
        )
    )
));
?>