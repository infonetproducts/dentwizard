<?php
// DEBUG version - Create order with detailed logging
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$servername = "localhost";
$username = "rwaf";
$password = "Py*uhb\$L\$##";
$dbname = "rwaf";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    echo json_encode(array('success' => false, 'error' => 'Database connection failed'));
    exit();
}

mysqli_set_charset($conn, "utf8");

// Get POST data
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

// DEBUG: Log the raw input
$debug_log = array();
$debug_log['raw_input'] = $input;
$debug_log['items_received'] = isset($input['items']) ? $input['items'] : 'NO ITEMS';

// Get authentication from request body
$auth_token = isset($input['auth_token']) ? $input['auth_token'] : '';
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;

if (!$auth_token || !$user_id) {
    echo json_encode(array(
        'success' => false, 
        'error' => 'Unauthorized - no valid token',
        'debug' => $debug_log
    ));
    exit();
}

// Verify user exists
$user_query = "SELECT id, name, email, BudgetBalance FROM Users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    echo json_encode(array(
        'success' => false, 
        'error' => 'User not found',
        'debug' => $debug_log
    ));
    exit();
}

$user_data = mysqli_fetch_assoc($user_result);
$current_budget = floatval($user_data['BudgetBalance']);

// Generate order ID
$order_id = date("md-His") . "-" . $user_id;

// Extract order data
$shipping = $input['shippingAddress'];
$payment_method = isset($input['paymentMethod']) ? $input['paymentMethod'] : 'budget';
$subtotal = floatval($input['subtotal']);
$shipping_cost = floatval($input['shippingCost']);
$tax = floatval($input['tax']);
$total = $subtotal + $shipping_cost + $tax;
$items = isset($input['items']) ? $input['items'] : array();

$debug_log['payment_method'] = $payment_method;
$debug_log['total_calculated'] = $total;
$debug_log['items_count'] = count($items);

// Check budget if using budget payment
if ($payment_method === 'budget' && $current_budget < $total) {
    echo json_encode(array(
        'success' => false, 
        'error' => 'Insufficient budget balance',
        'debug' => $debug_log
    ));
    exit();
}

// Start transaction
mysqli_autocommit($conn, FALSE);

try {
    // Insert main order - Using exact column names from original system
    $order_query = "INSERT INTO Orders (
        OrderID, UserID, OrderDate, OrderStatus,
        ShipToName, ShipToAdd1, ShipToAdd2, ShipToCity, ShipToState, ShipToZip,
        OrderTOTAL, ShippingCost, TotalSaleTax, PaymentMethod,
        Name, Email, CID, DueDate
    ) VALUES (
        ?, ?, NOW(), 'new',
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, 244, DATE_ADD(NOW(), INTERVAL 7 DAY)
    )";
    
    $stmt = mysqli_prepare($conn, $order_query);
    
    // Check if statement prepared successfully
    if (!$stmt) {
        throw new Exception("Failed to prepare order statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "sisssssssdddss",
        $order_id, $user_id,
        $shipping['name'], $shipping['address'], $shipping['address2'], 
        $shipping['city'], $shipping['state'], $shipping['zip'],
        $total, $shipping_cost, $tax, $payment_method,
        $user_data['name'], $user_data['email']
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to create order: " . mysqli_error($conn));
    }
    
    $order_record_id = mysqli_insert_id($conn);
    $debug_log['order_record_id'] = $order_record_id;
    
    // Insert order items with attributes - TRACKING EACH ITEM
    $items_inserted = array();
    
    foreach ($items as $index => $item) {
        $product_id = isset($item['product_id']) ? intval($item['product_id']) : 0;
        $product_name = isset($item['name']) ? $item['name'] : 'Product';
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $price = isset($item['price']) ? floatval($item['price']) : 0;
        
        // Get product attributes - LOG WHAT WE'RE GETTING
        $size = isset($item['size']) ? $item['size'] : '';
        $color = isset($item['color']) ? $item['color'] : '';
        $artwork = isset($item['artwork']) ? $item['artwork'] : '';
        
        // If artwork is not set, try 'logo' field
        if (empty($artwork) && isset($item['logo'])) {
            $artwork = $item['logo'];
        }
        
        $item_debug = array(
            'index' => $index,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'quantity' => $quantity,
            'price' => $price,
            'size_received' => $size,
            'color_received' => $color,
            'artwork_received' => $artwork,
            'original_item' => $item
        );
        
        $items_inserted[] = $item_debug;
        
        $form_id = 'REACT-' . $product_id . '-' . date('His');
        
        // Insert with exact column names from original system
        $item_insert_query = "INSERT INTO OrderItems (
            OrderRecordID, ItemID, FormID, FormDescription, 
            Quantity, Price,
            size_item, color_item, artwork_logo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $item_stmt = mysqli_prepare($conn, $item_insert_query);
        
        if (!$item_stmt) {
            throw new Exception("Failed to prepare item statement: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($item_stmt, "iissidsss",
            $order_record_id, $product_id, $form_id, $product_name,
            $quantity, $price,
            $size, $color, $artwork
        );
        
        if (!mysqli_stmt_execute($item_stmt)) {
            throw new Exception("Failed to insert order item at index $index: " . mysqli_error($conn));
        }
    }
    
    $debug_log['items_inserted'] = $items_inserted;
    
    // Update user's budget if using budget payment
    if ($payment_method === 'budget') {
        $new_budget = $current_budget - $total;
        $update_budget = "UPDATE Users SET BudgetBalance = ? WHERE id = ?";
        $budget_stmt = mysqli_prepare($conn, $update_budget);
        mysqli_stmt_bind_param($budget_stmt, "di", $new_budget, $user_id);
        
        if (!mysqli_stmt_execute($budget_stmt)) {
            throw new Exception("Failed to update budget: " . mysqli_error($conn));
        }
        
        $debug_log['budget_updated'] = array(
            'old_budget' => $current_budget,
            'new_budget' => $new_budget,
            'amount_deducted' => $total
        );
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode(array(
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'order_number' => $order_record_id,
        'redirect' => '/orders',
        'debug_info' => $debug_log
    ));
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => $debug_log
    ));
}

mysqli_close($conn);
?>