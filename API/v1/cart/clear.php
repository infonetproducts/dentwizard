<?php
// Clear cart after successful order - React API cart system
session_start();

// Include centralized CORS configuration
require_once __DIR__ . '/../../cors.php';

// Set content type
header("Content-Type: application/json");

// Clear React API cart session variable
$cart_cleared = false;

if (isset($_SESSION['cart_items'])) {
    unset($_SESSION['cart_items']);
    $cart_cleared = true;
}

// Also clear any cart summary/totals
if (isset($_SESSION['cart_summary'])) {
    unset($_SESSION['cart_summary']);
}

// Return success
echo json_encode(array(
    'success' => true,
    'message' => 'Cart cleared successfully',
    'cart_cleared' => $cart_cleared
));
?>
