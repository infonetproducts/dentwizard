<?php
// Clear cart after successful order - React API cart system
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token, X-User-Id, X-Session-ID");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
