<?php
// Create order with product attributes - Updated version
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
$input = json_decode(file_get_contents('php://input'), true);

// Get authentication from request body
$auth_token = isset($input['auth_token']) ? $input['auth_token'] : '';
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;

if (!$auth_token || !$user_id) {
    echo json_encode(array('success' => false, 'error' => 'Unauthorized - no valid token'));
    exit();
}

// Verify user exists
$user_query = "SELECT id, name, email, BudgetBalance FROM Users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    echo json_encode(array('success' => false, 'error' => 'User not found'));
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

// Check budget if using budget payment
if ($payment_method === 'budget' && $current_budget < $total) {
    echo json_encode(array('success' => false, 'error' => 'Insufficient budget balance'));
    exit();
}

// Start transaction
mysqli_autocommit($conn, FALSE);

try {
    // Insert main order
    $order_query = "INSERT INTO Orders (
        order_id, user_id, OrderDate, order_status,
        ship_name, ship_add1, ship_add2, ship_city, ship_state, ship_zip,
        order_total, shipping_charge, total_sale_tax, payment_method,
        Name, Email, Phone, Dept, BudgetBalance,
        order_place_by, ship_to_name, CID, DueDate
    ) VALUES (
        ?, ?, NOW(), 'new',
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, '', '', ?,
        ?, ?, 244, DATE_ADD(NOW(), INTERVAL 7 DAY)
    )";
    
    $stmt = mysqli_prepare($conn, $order_query);
    mysqli_stmt_bind_param($stmt, "sisssssssdddsssdssi",
        $order_id, $user_id,
        $shipping['name'], $shipping['address'], $shipping['address2'], 
        $shipping['city'], $shipping['state'], $shipping['zip'],
        $subtotal, $shipping_cost, $tax, $payment_method,
        $user_data['name'], $user_data['email'], $current_budget,
        $user_data['name'], $shipping['name']
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to create order: " . mysqli_error($conn));
    }
    
    $order_record_id = mysqli_insert_id($conn);
    
    // Insert order items with attributes (size, color, artwork)
    $item_insert_query = "INSERT INTO OrderItems (
        OrderRecordID, ItemID, FormID, FormDescription, 
        Quantity, Price,
        size_item, color_item, artwork_logo
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $item_stmt = mysqli_prepare($conn, $item_insert_query);
    
    foreach ($items as $item) {
        $product_id = isset($item['product_id']) ? intval($item['product_id']) : 0;
        $product_name = isset($item['name']) ? $item['name'] : 'Product';
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $price = isset($item['price']) ? floatval($item['price']) : 0;
        
        // Get product attributes - THESE ARE THE KEY FIELDS
        $size = isset($item['size']) ? $item['size'] : '';
        $color = isset($item['color']) ? $item['color'] : '';
        $artwork = isset($item['artwork']) ? $item['artwork'] : '';
        
        $form_id = 'FORM-' . $product_id;
        
        mysqli_stmt_bind_param($item_stmt, "iisssdsss",
            $order_record_id, $product_id, $form_id, $product_name,
            $quantity, $price,
            $size, $color, $artwork
        );
        
        if (!mysqli_stmt_execute($item_stmt)) {
            throw new Exception("Failed to insert order item: " . mysqli_error($conn));
        }
    }
    
    // Update user's budget if using budget payment
    if ($payment_method === 'budget') {
        $new_budget = $current_budget - $total;
        $update_budget = "UPDATE Users SET BudgetBalance = ? WHERE id = ?";
        $budget_stmt = mysqli_prepare($conn, $update_budget);
        mysqli_stmt_bind_param($budget_stmt, "di", $new_budget, $user_id);
        
        if (!mysqli_stmt_execute($budget_stmt)) {
            throw new Exception("Failed to update budget: " . mysqli_error($conn));
        }
    }
    
    // Clear the cart (if cart_items table exists)
    $clear_cart = "DELETE FROM cart_items WHERE user_id = ?";
    $cart_stmt = mysqli_prepare($conn, $clear_cart);
    if ($cart_stmt) {
        mysqli_stmt_bind_param($cart_stmt, "i", $user_id);
        mysqli_stmt_execute($cart_stmt); // Ignore errors as cart might be session-based
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode(array(
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'order_number' => $order_record_id,
        'redirect' => '/orders'
    ));
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage()
    ));
}

mysqli_close($conn);
?>