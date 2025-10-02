<?php
// Clear cart after successful order - React API cart system
session_start();

// Allowed origins for CORS
$allowed_origins = [
    'http://localhost:3000',
    'http://localhost:3001',
    'http://localhost:3002',
    'https://dentwizard.onrender.com',
    'https://dentwizard-prod.onrender.com',
    'https://dentwizard.lgstore.com',
    'https://dentwizardapparel.com',
    'https://www.dentwizardapparel.com'
];

// Try to determine origin from HTTP_ORIGIN or HTTP_REFERER
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// If no origin header, try to extract from referer
if (empty($origin) && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $parsed = parse_url($referer);
    if ($parsed) {
        $origin = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port']) && !in_array($parsed['port'], [80, 443])) {
            $origin .= ':' . $parsed['port'];
        }
    }
}

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost:3000");
}

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
