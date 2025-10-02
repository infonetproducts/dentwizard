<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

session_start();

if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'get';
$response = array();

switch ($action) {
    case 'add':
        // Add to cart
        $input = json_decode(file_get_contents('php://input'), true);
        $product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
        $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
        $size = isset($input['size']) ? $input['size'] : '';
        $color = isset($input['color']) ? $input['color'] : '';
        
        if (!$product_id) {
            echo json_encode(array('success' => false, 'message' => 'Product ID required'));
            exit;
        }
        
        // Get product info from database
        $mysqli = new mysqli('localhost', 'ujack102_ujack102', 'Cc$315921', 'ujack102_lgstore');
        
        if ($mysqli->connect_error) {
            echo json_encode(array('success' => false, 'message' => 'Database connection failed'));
            exit;
        }
        
        $query = "SELECT ID, item_title, Price, CID, ImageFile FROM wp_products WHERE ID = $product_id";
        $result = $mysqli->query($query);
        
        if (!$result || $result->num_rows == 0) {
            $mysqli->close();
            echo json_encode(array('success' => false, 'message' => 'Product not found'));
            exit;
        }
        
        $product = $result->fetch_assoc();
        
        // Start with default image
        $image_url = $product['ImageFile'] ? 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'] : '';
        
        // Try to get color-specific image if color is specified
        if (!empty($color)) {
            $color_query = "SELECT color_image FROM item_group_options WHERE item_id = $product_id AND display_name = '" . $mysqli->real_escape_string($color) . "' AND CID = 244 LIMIT 1";
            $color_result = $mysqli->query($color_query);
            
            if ($color_result && $color_result->num_rows > 0) {
                $color_data = $color_result->fetch_assoc();
                if (!empty($color_data['color_image'])) {
                    $image_url = 'https://dentwizard.lgstore.com/pdf/244/' . $color_data['color_image'];
                }
            }
        }
        
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
                'image' => $image_url
            );
        }
        
        $response = array('success' => true, 'message' => 'Added to cart');
        break;
        
    case 'get':
    default:
        // Get cart - simple response
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
        
    case 'remove':
        // Remove item
        $input = json_decode(file_get_contents('php://input'), true);
        $item_id = isset($input['item_id']) ? $input['item_id'] : '';
        
        foreach ($_SESSION['cart_items'] as $i => $item) {
            if ($item['id'] == $item_id) {
                unset($_SESSION['cart_items'][$i]);
                $_SESSION['cart_items'] = array_values($_SESSION['cart_items']);
                break;
            }
        }
        
        $response = array('success' => true, 'message' => 'Item removed');
        break;
        
    case 'clear':
        // Clear cart
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
if (!isset($response['data'])) {
    $response['data'] = array();
}

$response['data']['cart_items'] = array_values($_SESSION['cart_items']);
$response['data']['cart_summary'] = array(
    'total_items' => $total_items,
    'unique_items' => count($_SESSION['cart_items']),
    'subtotal' => $subtotal,
    'tax' => $tax,
    'shipping' => $shipping,
    'total' => $total
);
$response['data']['budget'] = array('has_budget' => false);
$response['data']['budget_status'] = array(
    'within_budget' => true,
    'can_checkout' => true,
    'message' => ''
);
$response['data']['user_authenticated'] = false;

echo json_encode($response);
?>