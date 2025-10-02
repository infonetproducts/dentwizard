<?php
// Single Cart Handler - Avoids problematic add.php filename
session_start();

// Allowed origins for CORS
$allowed_origins = [
    'https://dentwizard-app.onrender.com',
    'https://dentwizard.lgstore.com',
    'http://localhost:3000',
    'http://localhost:3001',
    'http://localhost:3002'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: " . $allowed_origins[0]);
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get action from query string or POST data
$action = isset($_GET['action']) ? $_GET['action'] : '';
if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = isset($input['action']) ? $input['action'] : 'add'; // Default to add for POST
}

// Initialize cart
if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

switch ($action) {
    case 'add':
        // Add to cart
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'error' => 'POST required for add'));
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
        $logo = isset($input['logo']) ? $input['logo'] : '';  // Add logo field
        
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
                // Also update the logo field if it was missing
                if (empty($_SESSION['cart_items'][$i]['logo']) && !empty($logo)) {
                    $_SESSION['cart_items'][$i]['logo'] = $logo;
                }
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
                'logo' => $logo,
                'image_url' => !empty($input['image']) ? $input['image'] : ($product['ImageFile'] ? 'https://dentwizard.lgstore.com/pdf/' . $product['CID'] . '/' . $product['ImageFile'] : '')
            );
        }
        
        $response = array('success' => true, 'message' => 'Added to cart');
        break;
        
    case 'get':
    default:
        // Get cart
        $response = array(
            'success' => true,
            'data' => array(
                'items' => array_values($_SESSION['cart_items'])
            )
        );
        break;
        
    case 'update':
        // Update quantity
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
        // Clear cart
        $_SESSION['cart_items'] = array();
        $response = array('success' => true, 'message' => 'Cart cleared');
        break;
}

// Calculate totals for all responses
$total_items = 0;
$subtotal = 0;

foreach ($_SESSION['cart_items'] as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['total'];
}

// Tax will be calculated at checkout based on shipping address using TaxJar
$tax = 0;  // No tax shown until checkout
$shipping = $subtotal > 100 ? 0 : 10;  // Free shipping over $100
$total = $subtotal + $shipping;  // Total without tax

// Add summary to response
$response['data']['summary'] = array(
    'total_items' => $total_items,
    'unique_items' => count($_SESSION['cart_items']),
    'subtotal' => round($subtotal, 2),
    'tax' => 0,  // Will be calculated at checkout with TaxJar
    'tax_note' => 'Tax calculated at checkout',
    'shipping' => round($shipping, 2),
    'total' => round($total, 2)
);

echo json_encode($response);
?>