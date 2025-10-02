<?php
// Minimal Add to Cart - Test Version
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Start session
session_start();

// Test without database first
$input = json_decode(file_get_contents('php://input'), true);

// Initialize cart if needed
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add test item
$_SESSION['cart'][] = array(
    'id' => '91754_XLT_Atlas',
    'product_id' => 91754,
    'name' => 'Test Product',
    'quantity' => 1,
    'price' => 65.00,
    'total' => 65.00,
    'size' => 'XLT',
    'color' => 'Atlas'
);

// Return success
echo json_encode(array(
    'success' => true,
    'message' => 'Test add successful - no database',
    'input_received' => $input,
    'cart_count' => count($_SESSION['cart'])
));
?>