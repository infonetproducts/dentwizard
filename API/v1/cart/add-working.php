<?php
// Add to Cart API - Based on Working LG Files Version
// This mimics the working shopping cart from lg_files

// Start session first
session_start();

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection - matching working detail.php
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    echo json_encode(array('success' => false, 'error' => 'Database connection failed'));
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!$input || !isset($input['product_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Product ID required'));
    exit();
}

$product_id = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
$size = isset($input['size']) ? $mysqli->real_escape_string($input['size']) : '';
$color = isset($input['color']) ? $mysqli->real_escape_string($input['color']) : '';

// Get product from database
$query = "SELECT ID, item_title, Price, ImageFile, CID, FormID FROM Items WHERE ID = $product_id AND (Active = 1 OR Active = 'Y') LIMIT 1";
$result = $mysqli->query($query);

if (!$result || $result->num_rows == 0) {
    echo json_encode(array('success' => false, 'error' => 'Product not found'));
    exit();
}

$product = $result->fetch_assoc();
$price = (float)$product['Price'];

// Initialize Order session like working app
if (!isset($_SESSION['Order'])) {
    $_SESSION['Order'] = array();
}

// Add to cart using same structure as working app
// The working app uses: $_SESSION['Order'][$item_id][] = $qty
// But for our React app, we need a more structured approach

// Create unique key for variant
$variant_key = $product_id . '_' . $size . '_' . $color;

// Initialize cart items array for React compatibility
if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

// Check if item with same variant exists
$found = false;
foreach ($_SESSION['cart_items'] as $index => $item) {
    if ($item['variant_key'] == $variant_key) {
        // Update quantity
        $_SESSION['cart_items'][$index]['quantity'] += $quantity;
        $_SESSION['cart_items'][$index]['total'] = $_SESSION['cart_items'][$index]['quantity'] * $_SESSION['cart_items'][$index]['price'];
        $found = true;
        break;
    }
}

// Add new item if not found
if (!$found) {
    // Build image URL like working app
    $image_url = '';
    if ($product['ImageFile']) {
        $image_url = 'https://dentwizard.lgstore.com/pdf/' . $product['CID'] . '/' . $product['ImageFile'];
    }
    
    $cart_item = array(
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
    
    $_SESSION['cart_items'][] = $cart_item;
}

// Also store in Order format for compatibility
$_SESSION['Order'][$product_id] = $quantity;

// Calculate totals
$total_items = 0;
$subtotal = 0;
$unique_items = 0;

if (isset($_SESSION['cart_items'])) {
    foreach ($_SESSION['cart_items'] as $item) {
        $total_items += $item['quantity'];
        $subtotal += $item['total'];
    }
    $unique_items = count($_SESSION['cart_items']);
}

// Calculate tax and shipping
$tax = $subtotal * 0.0825; // 8.25% tax
$shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
$total = $subtotal + $tax + $shipping;

// Close database
$mysqli->close();

// Return response
echo json_encode(array(
    'success' => true,
    'message' => 'Item added to cart',
    'session_id' => session_id(),
    'data' => array(
        'items' => isset($_SESSION['cart_items']) ? array_values($_SESSION['cart_items']) : array(),
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