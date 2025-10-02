<?php
// Create order - Corrected version using proper mysqli object style
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection - using your working pattern
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'error' => 'Database connection failed')));
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Get authentication
$auth_token = isset($input['auth_token']) ? $input['auth_token'] : '';
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;

if (!$auth_token || !$user_id) {
    die(json_encode(array('success' => false, 'error' => 'Unauthorized')));
}

// Verify user and get budget
$user_result = $mysqli->query("SELECT * FROM Users WHERE id = $user_id");
if (!$user_result || $user_result->num_rows == 0) {
    die(json_encode(array('success' => false, 'error' => 'User not found')));
}

$user_data = $user_result->fetch_assoc();
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

// Check budget
if ($payment_method === 'budget' && $current_budget < $total) {
    die(json_encode(array('success' => false, 'error' => 'Insufficient budget')));
}

// Start transaction
$mysqli->autocommit(FALSE);

try {
    // Prepare values with proper escaping
    $ship_name = $mysqli->real_escape_string($shipping['name']);
    $ship_add1 = $mysqli->real_escape_string($shipping['address']);
    $ship_add2 = $mysqli->real_escape_string($shipping['address2']);
    $ship_city = $mysqli->real_escape_string($shipping['city']);
    $ship_state = $mysqli->real_escape_string($shipping['state']);
    $ship_zip = $mysqli->real_escape_string($shipping['zip']);
    $user_name = $mysqli->real_escape_string($user_data['name']);
    $user_email = $mysqli->real_escape_string($user_data['email']);
    
    // Insert order
    $order_sql = "INSERT INTO Orders SET
        order_id = '$order_id',
        user_id = $user_id,
        OrderDate = NOW(),
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
        Name = '$user_name',
        Email = '$user_email',
        Phone = '',
        Dept = '',
        CID = 244,
        DueDate = DATE_ADD(NOW(), INTERVAL 7 DAY),
        order_place_by = '$user_name',
        ship_to_name = '$ship_name',
        BudgetBalance = $current_budget";
    
    if (!$mysqli->query($order_sql)) {
        throw new Exception("Order creation failed: " . $mysqli->error);
    }
    
    $order_record_id = $mysqli->insert_id;
    
    // Insert order items with attributes
    foreach ($items as $item) {
        $product_id = intval($item['product_id']);
        $product_name = $mysqli->real_escape_string($item['name']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        
        // Get product attributes - CRITICAL PART
        $size = isset($item['size']) ? $mysqli->real_escape_string($item['size']) : '';
        $color = isset($item['color']) ? $mysqli->real_escape_string($item['color']) : '';
        $artwork = isset($item['artwork']) ? $mysqli->real_escape_string($item['artwork']) : '';
        
        // Also check for 'logo' field
        if (empty($artwork) && isset($item['logo'])) {
            $artwork = $mysqli->real_escape_string($item['logo']);
        }
        
        $form_id = date("mdY_His");
        
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
        
        if (!$mysqli->query($item_sql)) {
            throw new Exception("Item insert failed: " . $mysqli->error);
        }
    }
    
    // Update budget
    if ($payment_method === 'budget') {
        $new_budget = $current_budget - $total;
        $budget_sql = "UPDATE Users SET BudgetBalance = $new_budget WHERE id = $user_id";
        
        if (!$mysqli->query($budget_sql)) {
            throw new Exception("Budget update failed: " . $mysqli->error);
        }
    }
    
    // Clear cart if table exists
    $mysqli->query("DELETE FROM cart_items WHERE user_id = $user_id");
    
    // Commit transaction
    $mysqli->commit();
    
    echo json_encode(array(
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'order_number' => $order_record_id
    ));
    
} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage()
    ));
}

$mysqli->close();
?>