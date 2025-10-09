<?php
// Single Cart Handler - Avoids problematic add.php filename

// Configure session for cross-origin requests (PHP 5.6 compatible)
// SameSite=None requires session_set_cookie_params
if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
    session_set_cookie_params(array(
        'lifetime' => 86400,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ));
} else {
    // PHP 5.6 - 7.2: Use older syntax
    session_set_cookie_params(86400, '/', '', true, true);
    // For PHP < 7.3, we need to manually set SameSite via header after session_start()
}

session_start();

// For PHP < 7.3, manually set SameSite=None via header
if (version_compare(PHP_VERSION, '7.3.0', '<')) {
    $sessionName = session_name();
    $sessionId = session_id();
    header('Set-Cookie: ' . $sessionName . '=' . $sessionId . '; path=/; secure; HttpOnly; SameSite=None', false);
}

// Include centralized CORS configuration
require_once __DIR__ . '/../../cors.php';

// Set content type
header("Content-Type: application/json");

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
        
        // Return the updated cart items after adding
        $response = array(
            'success' => true,
            'message' => 'Added to cart',
            'data' => array(
                'items' => array_values($_SESSION['cart_items'])
            )
        );
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
$client_id = null;

foreach ($_SESSION['cart_items'] as $item) {
    $total_items += $item['quantity'];
    $subtotal += $item['total'];
}

// Get client ID from the first item in cart (all items should be from same client)
if (!empty($_SESSION['cart_items'])) {
    // Connect to get client ID
    $mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
    if (!$mysqli->connect_error) {
        $first_product_id = $_SESSION['cart_items'][0]['product_id'];
        $query = "SELECT CID FROM Items WHERE ID = " . (int)$first_product_id . " LIMIT 1";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $client_id = (int)$row['CID'];
        }
        $mysqli->close();
    }
}

// Free shipping clients list
$free_shipping_clients = array(56, 59, 62, 63, 72, 78, 89, 244); // Added Dent Wizard (244)

// Calculate shipping
if (in_array($client_id, $free_shipping_clients)) {
    $shipping = 0; // Free shipping for specific clients
} else {
    $shipping = $subtotal > 100 ? 0 : 10;  // Free shipping over $100 for other clients
}

$tax = 0;  // No tax shown until checkout
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