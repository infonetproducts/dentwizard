<?php
// Cart Sync API - Restores cart from localStorage to PHP session
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Start session to store cart
session_start();

// Database connection - same as other APIs
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

$CID = 244; // DentWizard

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input
    $input_raw = file_get_contents('php://input');
    $input = json_decode($input_raw, true);
    
    if (isset($input['items']) && is_array($input['items'])) {
        // Clear existing cart
        $_SESSION['Order'] = array();
        $_SESSION['total_price'] = 0;
        
        $total = 0;
        $valid_items = array();
        
        // Restore each item to session
        foreach ($input['items'] as $item) {
            $product_id = intval($item['product_id']);
            $quantity = intval($item['quantity']);
            
            // Verify product exists
            $sql = "SELECT * FROM Items WHERE ID = $product_id AND CID = $CID AND status_item = 'Y' LIMIT 1";
            $result = $mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $product = $result->fetch_assoc();
                
                // Add to session cart
                $_SESSION['Order'][$product_id] = array(
                    'quantity' => $quantity,
                    'price' => $product['Price'],
                    'name' => $product['item_title']
                );
                
                $total += floatval($product['Price']) * $quantity;
                
                $valid_items[] = array(
                    'product_id' => $product_id,
                    'name' => $product['item_title'],
                    'price' => floatval($product['Price']),
                    'quantity' => $quantity,
                    'total' => floatval($product['Price']) * $quantity
                );
            }
        }
        
        $_SESSION['total_price'] = $total;
        
        // Return synced cart
        die(json_encode(array(
            'status' => 'success',
            'message' => 'Cart synced successfully',
            'items' => $valid_items,
            'summary' => array(
                'total_items' => count($valid_items),
                'subtotal' => $total,
                'total' => $total
            )
        )));
    } else {
        die(json_encode(array(
            'status' => 'error',
            'message' => 'No items to sync'
        )));
    }
}

// GET request - return current session cart
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $items = array();
    $total = 0;
    
    if (isset($_SESSION['Order']) && is_array($_SESSION['Order'])) {
        foreach ($_SESSION['Order'] as $product_id => $data) {
            $items[] = array(
                'product_id' => $product_id,
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'name' => $data['name']
            );
            $total += floatval($data['price']) * intval($data['quantity']);
        }
    }
    
    die(json_encode(array(
        'status' => 'success',
        'items' => $items,
        'summary' => array(
            'total_items' => count($items),
            'subtotal' => $total,
            'total' => $total
        )
    )));
}

$mysqli->close();
?>