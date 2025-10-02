<?php
// Cart handler - Fixed for session persistence
session_start();

// CORS headers - Allow credentials from React app
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';

// For development and production
if ($origin == 'http://localhost:3000' || $origin == 'http://localhost:3001' || strpos($origin, 'dentwizard') !== false) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : '';
if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = isset($input['action']) ? $input['action'] : 'add';
}

// Initialize cart
if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'error' => 'POST required'));
            exit();
        }
        
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
        $mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
        
        if ($mysqli->connect_error) {
            echo json_encode(array('success' => false, 'error' => 'Database error'));
            exit();
        }
        
        // Get product
        $query = "SELECT ID, item_title, Price, ImageFile, CID FROM Items WHERE ID = " . $product_id . " LIMIT 1";
        $result = $mysqli->query($query);
        
        if (!$result || $result->num_rows == 0) {
            echo json_encode(array('success' => false, 'error' => 'Product not found'));
            exit();
        }
        
        $product = $result->fetch_assoc();
        $mysqli->close();
        
        // Add to cart
        $cart_key = $product_id . '_' . $size . '_' . $color;
        $price = (float)$product['Price'];
        
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
        
        if (!$found) {
            $_SESSION['cart_items'][] = array(
                'id' => $cart_key,
                'product_id' => $product_id,
                'name' => $product['item_title'],
                'quantity' => $quantity,
                'price' => $price,
                'total' => $price * $quantity,
                'size' => $size,
                'color' => $color,
                'image_url' => $product['ImageFile'] ? 'https://dentwizard.lgstore.com/pdf/' . $product['CID'] . '/' . $product['ImageFile'] : ''
            );
        }
        
        $response = array('success' => true, 'message' => 'Added to cart', 'session_id' => session_id());
        break;
        
    case 'get':
    default:
        $response = array('success' => true, 'session_id' => session_id());
        break;
        
    case 'update':
        $input = json_decode(file_get_contents('php://input'), true);
        $item_id = isset($input['item_id']) ? $input['item_id'] : '';
        $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;
        
        foreach ($_SESSION['cart_items'] as $i => $item) {
            if ($item['id'] == $item_id) {
                if ($quantity == 0) {
                    unset($_SESSION['cart_items'][$i]);
                    $_SESSION['cart_items'] = array_values($_SESSION['cart_items']);
                } else {
                    $_SESSION['cart_items'][$i]['quantity'] = $quantity;
                    $_SESSION['cart_items'][$i]['total'] = $_SESSION['cart_items'][$i]['price'] * $quantity;
                }
                break;
            }
        }
        
        $response = array('success' => true, 'message' => 'Updated');
        break;
        
    case 'clear':
        $_SESSION['cart_items'] = array();
        $response = array('success' => true, 'message' => 'Cart cleared');
        break;
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

// Add data to response
$response['data'] = array(
    'items' => array_values($_SESSION['cart_items']),
    'summary' => array(
        'total_items' => $total_items,
        'unique_items' => count($_SESSION['cart_items']),
        'subtotal' => round($subtotal, 2),
        'tax' => round($tax, 2),
        'shipping' => round($shipping, 2),
        'total' => round($total, 2)
    )
);

echo json_encode($response);
?>