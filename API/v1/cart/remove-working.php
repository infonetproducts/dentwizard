<?php
// Remove from Cart - Working Version

session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['item_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Item ID required'));
    exit();
}

$item_id = $input['item_id'];

if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

// Find and remove item
$item_found = false;
foreach ($_SESSION['cart_items'] as $index => $item) {
    if ($item['id'] == $item_id) {
        unset($_SESSION['cart_items'][$index]);
        $_SESSION['cart_items'] = array_values($_SESSION['cart_items']); // Re-index
        $item_found = true;
        break;
    }
}

if (!$item_found) {
    echo json_encode(array('success' => false, 'error' => 'Item not found in cart'));
    exit();
}

// Calculate new totals
$total_items = 0;
$subtotal = 0;
$unique_items = count($_SESSION['cart_items']);

foreach ($_SESSION['cart_items'] as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['total'];
}

// Calculate tax and shipping
$tax = $subtotal * 0.0825;
$shipping = $subtotal > 100 ? 0 : 10;
$total = $subtotal + $tax + $shipping;

// Return response
echo json_encode(array(
    'success' => true,
    'message' => 'Item removed from cart',
    'data' => array(
        'items' => array_values($_SESSION['cart_items']),
        'summary' => array(
            'total_items' => $total_items,
            'unique_items' => $unique_items,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2)
        )
    )
));
?>