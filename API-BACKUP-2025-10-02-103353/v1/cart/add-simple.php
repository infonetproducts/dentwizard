<?php
// Cart Add API - SIMPLE WORKING VERSION for PHP 5.6

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");

// CRITICAL: Set content type BEFORE any output
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start output buffering to catch any errors
ob_start();

// Disable ALL error output to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

// Start session
@session_start();

// Function to output JSON and exit
function outputJSON($data) {
    // Clear any previous output
    ob_clean();
    echo json_encode($data);
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    outputJSON(array(
        'success' => false,
        'error' => 'Product ID and quantity are required'
    ));
}

// Get values
$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];
$size = isset($input['size']) ? $input['size'] : '';
$color = isset($input['color']) ? $input['color'] : '';

// Validate quantity
if ($quantity < 1) {
    outputJSON(array(
        'success' => false,
        'error' => 'Quantity must be at least 1'
    ));
}

// Database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    outputJSON(array(
        'success' => false,
        'error' => 'Database connection failed'
    ));
}

// Get product from database
$query = "SELECT ID, item_title, Price, ImageFile, CID, FormID FROM Items WHERE ID = ? AND (Active = 1 OR Active = 'Y')";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    outputJSON(array(
        'success' => false,
        'error' => 'Product not found'
    ));
}

$product = $result->fetch_assoc();
$stmt->close();

// Initialize cart if needed
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Create cart item
$cart_item_id = $product_id . '_' . $size . '_' . $color;
$unit_price = (float)$product['Price'];

// Check if item exists in cart
$found = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $cart_item_id) {
        // Update quantity
        $_SESSION['cart'][$key]['quantity'] += $quantity;
        $_SESSION['cart'][$key]['total'] = $_SESSION['cart'][$key]['price'] * $_SESSION['cart'][$key]['quantity'];
        $found = true;
        break;
    }
}

// Add new item if not found
if (!$found) {
    $image_url = '';
    if ($product['ImageFile']) {
        $image_url = 'https://dentwizard.lgstore.com/pdf/' . $product['CID'] . '/' . $product['ImageFile'];
    }
    
    $_SESSION['cart'][] = array(
        'id' => $cart_item_id,
        'product_id' => $product_id,
        'name' => $product['item_title'],
        'sku' => $product['FormID'],
        'quantity' => $quantity,
        'price' => $unit_price,
        'total' => $unit_price * $quantity,
        'size' => $size,
        'color' => $color,
        'image_url' => $image_url
    );
}

// Calculate totals
$total_items = 0;
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['total'];
}

// Calculate tax and shipping
$tax = $subtotal * 0.0825;
$shipping = $subtotal > 100 ? 0 : 10;
$total = $subtotal + $tax + $shipping;

// Close database
$mysqli->close();

// Return success response
outputJSON(array(
    'success' => true,
    'message' => 'Item added to cart',
    'session_id' => session_id(),
    'data' => array(
        'items' => array_values($_SESSION['cart']),
        'summary' => array(
            'total_items' => $total_items,
            'unique_items' => count($_SESSION['cart']),
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2)
        )
    )
));
?>