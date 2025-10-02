<?php
// Create order - Fixed version for PHP 5.6 with correct column names
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

// Get authentication
$auth_token = isset($input['auth_token']) ? $input['auth_token'] : '';
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;

if (!$auth_token || !$user_id) {
    echo json_encode(array('success' => false, 'error' => 'Unauthorized'));
    exit();
}

// Verify user exists and get budget
$user_query = "SELECT * FROM Users WHERE id = " . $user_id;
$user_result = mysqli_query($conn, $user_query);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    echo json_encode(array('success' => false, 'error' => 'User not found'));
    exit();
}

$user_data = mysqli_fetch_assoc($user_result);
$current_budget = floatval($user_data['BudgetBalance']);
$CID = 244; // Client ID for DentWizard

// Generate order ID like original system
$order_id = date("md-His") . "-" . $user_id;

// Extract order data
$shipping = isset($input['shippingAddress']) ? $input['shippingAddress'] : array();
$payment_method = isset($input['paymentMethod']) ? $input['paymentMethod'] : 'budget';
$subtotal = isset($input['subtotal']) ? floatval($input['subtotal']) : 0;
$shipping_cost = isset($input['shippingCost']) ? floatval($input['shippingCost']) : 10;
$tax = isset($input['tax']) ? floatval($input['tax']) : 0;
$total = $subtotal + $shipping_cost + $tax;
$items = isset($input['items']) ? $input['items'] : array();

// Check budget if using budget payment
if ($payment_method === 'budget' && $current_budget < $total) {
    echo json_encode(array('success' => false, 'error' => 'Insufficient budget'));
    exit();
}

// Start transaction
mysqli_autocommit($conn, FALSE);

try {
    // Prepare shipping info
    $ship_name = isset($shipping['name']) ? mysqli_real_escape_string($conn, $shipping['name']) : '';
    $ship_add1 = isset($shipping['address']) ? mysqli_real_escape_string($conn, $shipping['address']) : '';
    $ship_add2 = isset($shipping['address2']) ? mysqli_real_escape_string($conn, $shipping['address2']) : '';
    $ship_city = isset($shipping['city']) ? mysqli_real_escape_string($conn, $shipping['city']) : '';
    $ship_state = isset($shipping['state']) ? mysqli_real_escape_string($conn, $shipping['state']) : '';
    $ship_zip = isset($shipping['zip']) ? mysqli_real_escape_string($conn, $shipping['zip']) : '';
    
    $order_date = date("Y-m-d H:i:s");
    $due_date = date("Y-m-d", strtotime("+7 days"));
    
    // Insert order - using exact column names from original system
    $order_sql = "INSERT INTO Orders SET
        order_id = '$order_id',
        user_id = $user_id,
        OrderDate = '$order_date',
        order_status = 'new',
        ship_name = '$ship_name',
        ship_add1 = '$ship_add1',
        ship_add2 = '$ship_add2',
        ship_city = '$ship_city',
        ship_state = '$ship_state',
        ship_zip = '$ship_zip',
        order_total = $subtotal,
        shipping_charge = $shipping_cost,
        total_sale_tax = $tax,
        payment_method = '$payment_method',
        Name = '" . mysqli_real_escape_string($conn, $user_data['name']) . "',
        Email = '" . mysqli_real_escape_string($conn, $user_data['email']) . "',
        CID = $CID,
        DueDate = '$due_date',
        order_place_by = '" . mysqli_real_escape_string($conn, $user_data['name']) . "',
        ship_to_name = '$ship_name',
        BudgetBalance = $current_budget";
    
    if (!mysqli_query($conn, $order_sql)) {
        throw new Exception("Failed to create order: " . mysqli_error($conn));
    }
    
    $order_record_id = mysqli_insert_id($conn);
    
    // Insert order items with attributes
    foreach ($items as $item) {
        $product_id = isset($item['product_id']) ? intval($item['product_id']) : 0;
        $product_name = isset($item['name']) ? mysqli_real_escape_string($conn, $item['name']) : '';
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $price = isset($item['price']) ? floatval($item['price']) : 0;
        
        // Get product attributes - THESE ARE CRITICAL
        $size = isset($item['size']) ? mysqli_real_escape_string($conn, $item['size']) : '';
        $color = isset($item['color']) ? mysqli_real_escape_string($conn, $item['color']) : '';
        $artwork = isset($item['artwork']) ? mysqli_real_escape_string($conn, $item['artwork']) : '';
        
        // If artwork is empty, try logo field
        if (empty($artwork) && isset($item['logo'])) {
            $artwork = mysqli_real_escape_string($conn, $item['logo']);
        }
        
        // Generate FormID like original system
        $form_id = date("mdY_Gis", time());
        
        // Insert order item with attributes
        $item_sql = "INSERT INTO OrderItems SET
            OrderRecordID = $order_record_id,
            ID = 1,
            ItemID = $product_id,
            FormID = '$form_id',
            FormDescription = '$product_name',
            Quantity = $quantity,
            Price = $price,
            size_item = '$size',
            color_item = '$color',
            artwork_logo = '$artwork',
            Shipped = 0,
            Invoiced = 0,
            BOQuantity = 0,
            QtyShipped = 0";
        
        if (!mysqli_query($conn, $item_sql)) {
            throw new Exception("Failed to insert order item: " . mysqli_error($conn));
        }
    }
    
    // Update user's budget if using budget payment
    if ($payment_method === 'budget') {
        $new_budget = $current_budget - $total;
        $budget_sql = "UPDATE Users SET BudgetBalance = $new_budget WHERE id = $user_id";
        
        if (!mysqli_query($conn, $budget_sql)) {
            throw new Exception("Failed to update budget: " . mysqli_error($conn));
        }
    }
    
    // Clear the cart (if cart_items table exists)
    $clear_cart_sql = "DELETE FROM cart_items WHERE user_id = $user_id";
    mysqli_query($conn, $clear_cart_sql); // Ignore errors as cart might be session-based
    
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