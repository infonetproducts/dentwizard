<?php
// api/v1/cart/add.php
// PHP 5.6 Compatible Add to Cart Endpoint

// CORS Headers - MUST be at the very top
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';

// Session-based cart (doesn't require authentication)
if (session_id() === '') {
    session_start();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Product ID and quantity are required'
    ));
    exit;
}

$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];
$size = isset($input['size']) ? $input['size'] : null;
$color = isset($input['color']) ? $input['color'] : null;
$custom_name = isset($input['custom_name']) ? $input['custom_name'] : null;
$custom_number = isset($input['custom_number']) ? $input['custom_number'] : null;
$logo_option = isset($input['logo_option']) ? $input['logo_option'] : null;

// Validate quantity
if ($quantity < 1) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Quantity must be at least 1'
    ));
    exit;
}

// Get product details from database with CORRECT column names
$pdo = getPDOConnection();
$base_url = getBaseUrl();

$stmt = $pdo->prepare("
    SELECT 
        id, 
        item_title, 
        Price, 
        ImageFile,
        Size as available_sizes, 
        Color as available_colors,
        QtyPrice as quantity_pricing, 
        CID,
        FormID
    FROM Items 
    WHERE id = :product_id AND (Active = 1 OR Active = 'Y')
");
$stmt->execute(array('product_id' => $product_id));
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo json_encode(array(
        'success' => false,
        'error' => 'Product not found'
    ));
    exit;
}

// Calculate price based on quantity pricing
$unit_price = (float)$product['Price'];

if ($product['quantity_pricing']) {
    $qty_prices = explode(',', $product['quantity_pricing']);
    foreach ($qty_prices as $qty_price) {
        if (strpos($qty_price, ':') !== false) {
            list($min_qty, $price) = explode(':', $qty_price);
            if ($quantity >= (int)$min_qty) {
                $unit_price = (float)$price;
            }
        }
    }
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Create cart item
$cart_item_key = $product_id . '_' . $size . '_' . $color;
$cart_item = array(
    'product_id' => $product_id,
    'product_name' => $product['item_title'],
    'sku' => $product['FormID'],
    'quantity' => $quantity,
    'unit_price' => $unit_price,
    'total_price' => $unit_price * $quantity,
    'size' => $size,
    'color' => $color,
    'custom_name' => $custom_name,
    'custom_number' => $custom_number,
    'logo_option' => $logo_option,
    'image_url' => $product['ImageFile'] ? 
                   $base_url . '/pdf/' . $product['CID'] . '/' . $product['ImageFile'] : 
                   null
);

// Check if item already exists in cart
$item_exists = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['product_id'] == $product_id && 
        $item['size'] == $size && 
        $item['color'] == $color) {
        // Update existing item
        $_SESSION['cart'][$key]['quantity'] += $quantity;
        $_SESSION['cart'][$key]['total_price'] = 
            $_SESSION['cart'][$key]['unit_price'] * $_SESSION['cart'][$key]['quantity'];
        $item_exists = true;
        break;
    }
}

// Add new item if doesn't exist
if (!$item_exists) {
    $_SESSION['cart'][] = $cart_item;
}

// Calculate cart totals
$cart_total = 0;
$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['total_price'];
    $total_items += $item['quantity'];
}

// Return response
$response = array(
    'success' => true,
    'message' => 'Item added to cart',
    'data' => array(
        'cart_items' => array_values($_SESSION['cart']),
        'cart_summary' => array(
            'total_items' => $total_items,
            'subtotal' => $cart_total,
            'item_count' => count($_SESSION['cart'])
        )
    )
);

echo json_encode($response);
?>