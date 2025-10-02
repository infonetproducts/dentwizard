<?php
// Alternative name: item-add.php (avoiding "add.php" filename)
// Exact same code as add-simplest.php but different filename

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

if (!$input || !isset($input['product_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Product ID required'));
    exit();
}

$product_id = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
$size = isset($input['size']) ? $input['size'] : '';
$color = isset($input['color']) ? $input['color'] : '';

// Connect to database
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    echo json_encode(array('success' => false, 'error' => 'Database error'));
    exit();
}

// Get product
$query = "SELECT ID, item_title, Price, ImageFile, CID, FormID FROM Items WHERE ID = " . $product_id . " LIMIT 1";
$result = $mysqli->query($query);

if (!$result || $result->num_rows == 0) {
    echo json_encode(array('success' => false, 'error' => 'Product not found'));
    exit();
}

$product = $result->fetch_assoc();
$mysqli->close();

// Initialize cart
if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

// Create cart key
$cart_key = $product_id . '_' . $size . '_' . $color;

// Check if exists
$found = false;
foreach ($_SESSION['cart_items'] as $i => $item) {
    if ($item['id'] == $cart_key) {
        $_SESSION['cart_items'][$i]['quantity'] += $quantity;
        $_SESSION['cart_items'][$i]['total'] = $_SESSION['cart_items'][$i]['price'] * $_SESSION['cart_items'][$i]['quantity'];
        $found = true;
        break;
    }
}

// Add new if not found
if (!$found) {
    $price = (float)$product['Price'];
    
    $new_item = array(
        'id' => $cart_key,
        'product_id' => $product_id,
        'name' => $product['item_title'],
        'sku' => $product['FormID'],
        'quantity' => $quantity,
        'price' => $price,
        'total' => $price * $quantity,
        'size' => $size,
        'color' => $color,
        'image_url' => $product['ImageFile'] ? 'https://dentwizard.lgstore.com/pdf/' . $product['CID'] . '/' . $product['ImageFile'] : ''
    );
    
    $_SESSION['cart_items'][] = $new_item;
}

// Calculate totals
$total_items = 0;
$subtotal = 0;

foreach ($_SESSION['cart_items'] as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['total'];
}

$tax = $subtotal * 0.0825;
$shipping = $subtotal > 100 ? 0 : 10;
$total = $subtotal + $tax + $shipping;

// Return response
echo json_encode(array(
    'success' => true,
    'message' => 'Added to cart',
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