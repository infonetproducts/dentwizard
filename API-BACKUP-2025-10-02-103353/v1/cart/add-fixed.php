<?php
// Add to Cart - FIXED VERSION
// Fixed the mysqli->real_escape_string() issue

session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get input FIRST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Product ID required'));
    exit();
}

// Get basic values BEFORE database connection
$product_id = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

// Connect to database FIRST
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    echo json_encode(array('success' => false, 'error' => 'Database connection failed'));
    exit();
}

// NOW we can use real_escape_string
$size = isset($input['size']) ? $mysqli->real_escape_string($input['size']) : '';
$color = isset($input['color']) ? $mysqli->real_escape_string($input['color']) : '';

// Get product
$query = "SELECT ID, item_title, Price, ImageFile, CID, FormID FROM Items WHERE ID = $product_id AND (Active = 1 OR Active = 'Y') LIMIT 1";
$result = $mysqli->query($query);

if (!$result || $result->num_rows == 0) {
    echo json_encode(array('success' => false, 'error' => 'Product not found'));
    $mysqli->close();
    exit();
}

$product = $result->fetch_assoc();
$price = (float)$product['Price'];

// Initialize cart
if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

// Create variant key
$variant_key = $product_id . '_' . $size . '_' . $color;

// Check if exists
$found = false;
foreach ($_SESSION['cart_items'] as $index => $item) {
    if (isset($item['variant_key']) && $item['variant_key'] == $variant_key) {
        $_SESSION['cart_items'][$index]['quantity'] += $quantity;
        $_SESSION['cart_items'][$index]['total'] = $_SESSION['cart_items'][$index]['quantity'] * $_SESSION['cart_items'][$index]['price'];
        $found = true;
        break;
    }
}

// Add new if not found
if (!$found) {
    $image_url = '';
    if ($product['ImageFile']) {
        $image_url = 'https://dentwizard.lgstore.com/pdf/' . $product['CID'] . '/' . $product['ImageFile'];
    }
    
    $_SESSION['cart_items'][] = array(
        'id' => $variant_key,
        'variant_key' => $variant_key,
        'product_id' => $product_id,
        'name' => $product['item_title'],
        'sku' => $product['FormID'],
        'quantity' => $quantity,
        'price' => $price,
        'total' => $price * $quantity,
        'size' => $size,
        'color' => $color,
        'image_url' => $image_url
    );
}

// Calculate totals
$total_items = 0;
$subtotal = 0;

foreach ($_SESSION['cart_items'] as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['total'];
}

// Calculate tax and shipping
$tax = $subtotal * 0.0825;
$shipping = $subtotal > 100 ? 0 : 10;
$total = $subtotal + $tax + $shipping;

// Close database
$mysqli->close();

// Return response
echo json_encode(array(
    'success' => true,
    'message' => 'Item added to cart',
    'session_id' => session_id(),
    'data' => array(
        'items' => array_values($_SESSION['cart_items']),
        'summary' => array(
            'total_items' => $total_items,
            'unique_items' => count($_SESSION['cart_items']),
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2)
        )
    )
));
?>