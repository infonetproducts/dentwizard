<?php
// CORS Headers - MUST BE FIRST
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type
header('Content-Type: application/json');

// Start session for cart management
session_start();

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart items
$cart_items = $_SESSION['cart'];

// Calculate totals
$subtotal = 0;
$item_count = 0;

foreach ($cart_items as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
    $item_count += $item['quantity'];
}

// Return cart data
echo json_encode([
    'success' => true,
    'data' => [
        'items' => array_values($cart_items),
        'subtotal' => $subtotal,
        'tax' => $subtotal * 0.08, // 8% tax - adjust as needed
        'total' => $subtotal * 1.08,
        'item_count' => $item_count
    ]
], JSON_PRETTY_PRINT);
?>
