<?php
// Update Cart Quantity - Working Version

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

if (!$input || !isset($input['item_id']) || !isset($input['quantity'])) {
    echo json_encode(array('success' => false, 'error' => 'Item ID and quantity required'));
    exit();
}

$item_id = $input['item_id'];
$new_quantity = (int)$input['quantity'];

if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

// Find and update item
$item_found = false;
if ($new_quantity === 0) {
    // Remove item
    foreach ($_SESSION['cart_items'] as $index => $item) {
        if ($item['id'] == $item_id) {
            unset($_SESSION['cart_items'][$index]);
            $_SESSION['cart_items'] = array_values($_SESSION['cart_items']); // Re-index
            $item_found = true;
            break;
        }
    }
} else {
    // Update quantity
    foreach ($_SESSION['cart_items'] as $index => $item) {
        if ($item['id'] == $item_id) {
            $_SESSION['cart_items'][$index]['quantity'] = $new_quantity;
            $_SESSION['cart_items'][$index]['total'] = $_SESSION['cart_items'][$index]['price'] * $new_quantity;
            $item_found = true;
            break;
        }
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
    'message' => $new_quantity === 0 ? 'Item removed' : 'Quantity updated',
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