<?php
// CORS Headers - MUST be at the very top
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection
require_once '../../shop_common_function.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['items']) || !is_array($input['items']) || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order data']);
    exit;
}

// Create order (simplified - add your actual order creation logic)
$order_id = uniqid('ORD-');
$total = 0;

foreach ($input['items'] as $item) {
    $total += $item['quantity'] * $item['price'];
}

// Return order confirmation
echo json_encode([
    'success' => true,
    'order_id' => $order_id,
    'total' => $total,
    'status' => 'pending',
    'message' => 'Order created successfully'
]);
?>