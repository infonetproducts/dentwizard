<?php
// Order Creation API - FIXED WITH CORRECT TABLE NAME
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(array('success' => false, 'message' => 'Method not allowed')));
}

// Get input
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

if (!$input) {
    die(json_encode(array('success' => false, 'message' => 'Invalid input')));
}

// Get authentication from body
$token = isset($input['auth_token']) ? $input['auth_token'] : null;
$user_id = isset($input['user_id']) ? $input['user_id'] : null;

if (!$token || !$user_id) {
    die(json_encode(array('success' => false, 'message' => 'Missing authentication')));
}

// Verify token format
$decoded = base64_decode($token);
if (!$decoded) {
    die(json_encode(array('success' => false, 'message' => 'Invalid token format')));
}

$parts = explode(':', $decoded);
if (count($parts) !== 3 || $parts[0] != $user_id) {
    die(json_encode(array('success' => false, 'message' => 'Token validation failed')));
}

// Get user from database - USING CORRECT TABLE NAME: Users (capital U)
$query = "SELECT ID, Name, Email, Login FROM Users WHERE ID = " . intval($user_id);
$result = $mysqli->query($query);

if (!$result || $result->num_rows === 0) {
    die(json_encode(array('success' => false, 'message' => 'User not found')));
}

$user = $result->fetch_assoc();
$CID = 244; // DentWizard client ID

// Generate OrderID
$order_id = date("md-His") . "-" . $user_id;

// Extract data from React's structure
$shipping = isset($input['shippingAddress']) ? $input['shippingAddress'] : array();
$items = isset($input['items']) ? $input['items'] : array();
$subtotal = isset($input['subtotal']) ? floatval($input['subtotal']) : 0;
$tax_amount = isset($input['tax']) ? floatval($input['tax']) : 0;
$shipping_cost = isset($input['shippingCost']) ? floatval($input['shippingCost']) : 0;
$total = isset($input['total']) ? floatval($input['total']) : 0;
$payment_method = isset($input['paymentMethod']) ? $mysqli->real_escape_string($input['paymentMethod']) : 'Credit Card';

// Prepare shipping data
$name = isset($shipping['name']) ? $mysqli->real_escape_string($shipping['name']) : $user['Name'];
$address1 = isset($shipping['address']) ? $mysqli->real_escape_string($shipping['address']) : '';
$city = isset($shipping['city']) ? $mysqli->real_escape_string($shipping['city']) : '';
$state = isset($shipping['state']) ? $mysqli->real_escape_string($shipping['state']) : '';
$zip = isset($shipping['zip']) ? $mysqli->real_escape_string($shipping['zip']) : '';
$phone = isset($shipping['phone']) ? $mysqli->real_escape_string($shipping['phone']) : '';
$email = $user['Email'];
$user_login = isset($user['Login']) ? $user['Login'] : $email;

// Set dates
$order_date = date('Y-m-d H:i:s');
$due_date = date('Y-m-d', strtotime('+7 days'));

// Insert order - with order total, tax, and shipping
$query = "INSERT INTO Orders (
    OrderID, OrderDate, UserID, UserLogin, Email, 
    Name, Dept, DueDate, Company, 
    ShipToName, ShipToDept, Phone, 
    Address1, Address2, City, State, Zip, 
    Notes, BillCode, BillTo, Status, ShipDate, CID,
    order_total, total_sale_tax, shipping_charge
) VALUES (
    '$order_id', '$order_date', '$user_id', '$user_login', '$email',
    '$name', '', '$due_date', '',
    '$name', '', '$phone',
    '$address1', '', '$city', '$state', '$zip',
    '', 0, '', 'new', '$order_date', '$CID',
    '$total', '$tax_amount', '$shipping_cost'
)";

$result = $mysqli->query($query);

if (!$result) {
    die(json_encode(array(
        'success' => false, 
        'message' => 'Failed to create order',
        'error' => $mysqli->error
    )));
}

// Get the inserted order's ID
$order_table_id = $mysqli->insert_id;

// Insert order items
foreach ($items as $item) {
    $product_id = isset($item['product_id']) ? intval($item['product_id']) : 0;
    $product_name = isset($item['name']) ? $mysqli->real_escape_string($item['name']) : '';
    $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
    $price = isset($item['price']) ? floatval($item['price']) : 0;
    
    // Get product attributes (size, color, logo)
    $size = isset($item['size']) ? $mysqli->real_escape_string($item['size']) : '';
    $color = isset($item['color']) ? $mysqli->real_escape_string($item['color']) : '';
    $logo = isset($item['logo']) ? $mysqli->real_escape_string($item['logo']) : '';
    
    // Fetch actual product SKU from Items table
    $sku = '';
    if ($product_id > 0) {
        $sku_query = "SELECT FormID FROM Items WHERE ID = $product_id LIMIT 1";
        $sku_result = $mysqli->query($sku_query);
        if ($sku_result && $sku_result->num_rows > 0) {
            $sku_row = $sku_result->fetch_assoc();
            $sku = $sku_row['FormID'];
        }
    }
    
    // Fallback to generated SKU if not found
    if (empty($sku)) {
        $sku = $product_id . '_' . time();
    }
    
    // Insert item with size, color, and logo attributes
    $item_query = "INSERT INTO OrderItems (
        OrderRecordID, ID, ItemID, FormID, FormDescription, 
        Quantity, BOQuantity, QtyShipped, Price, 
        Shipped, Invoiced, size_item, color_item, artwork_logo
    ) VALUES (
        '$order_table_id', 0, '$product_id', '$sku', '$product_name',
        '$quantity', 0, 0, '$price',
        0, 0, '$size', '$color', '$logo'
    )";
    
    $item_result = $mysqli->query($item_query);
}

// Get current budget before update
$budget_result = $mysqli->query("SELECT Budget, BudgetBalance FROM Users WHERE ID = $user_id");
if (!$budget_result) {
    die(json_encode(array(
        'success' => false,
        'message' => 'Failed to retrieve user budget'
    )));
}
$budget_row = $budget_result->fetch_assoc();
$current_budget = $budget_row['Budget'];
$current_balance = $budget_row['BudgetBalance'];

// Store previous budget amount in order
$mysqli->query("UPDATE Orders SET previous_budget_amount = '$current_balance' WHERE ID = $order_table_id");

// Update user's budget balance - USING CORRECT FIELD: BudgetBalance
$budget_query = "UPDATE Users SET BudgetBalance = BudgetBalance - $total WHERE ID = $user_id";
$budget_update_result = $mysqli->query($budget_query);

if (!$budget_update_result) {
    die(json_encode(array(
        'success' => false,
        'message' => 'Failed to update budget balance',
        'error' => $mysqli->error
    )));
}

// Check if budget was actually updated
if ($mysqli->affected_rows > 0) {
    // Get new balance after update
    $new_balance_result = $mysqli->query("SELECT BudgetBalance FROM Users WHERE ID = $user_id");
    $new_balance_row = $new_balance_result->fetch_assoc();
    $new_balance = $new_balance_row['BudgetBalance'];
    
    // Log budget history
    $order_dtm = date('Y-m-d H:i:s');
    $created_dtm = date('Y-m-d H:i:s');
    
    $history_query = "INSERT INTO budget_history SET
        order_dtm = '$order_dtm',
        order_id = '$order_id',
        or_id = '$order_table_id',
        user_id = '$user_id',
        cid = '$CID',
        amount = '$total',
        order_time_BudgetBalance = '$current_balance',
        after_order_BudgetBalance = '$new_balance',
        reason = 'new order created',
        reason_label = 'new_order_created',
        created_dtm = '$created_dtm'";
    $mysqli->query($history_query);
    
    // Log to budget_log_all_trans
    $log_type = 'create_order';
    $action_title = 'Create Order';
    $log_query = "INSERT INTO budget_log_all_trans SET
        cid = '$CID',
        user_id = '$user_id',
        action_title = '$action_title',
        log_type = '$log_type',
        id = '$order_table_id',
        created_dtm = '$created_dtm'";
    $mysqli->query($log_query);
    $log_id = $mysqli->insert_id;
    
    // Log budget detail changes
    $detail_query1 = "INSERT INTO budget_log_all_trans_detail SET
        log_id = '$log_id',
        field_name = 'Budget',
        old_value = '$current_budget',
        new_value = '$current_budget',
        created_dtm = '$created_dtm'";
    $mysqli->query($detail_query1);
    
    $detail_query2 = "INSERT INTO budget_log_all_trans_detail SET
        log_id = '$log_id',
        field_name = 'BudgetBalance',
        old_value = '$current_balance',
        new_value = '$new_balance',
        created_dtm = '$created_dtm',
        order_total = '$total'";
    $mysqli->query($detail_query2);
}

// Return success response
die(json_encode(array(
    'success' => true,
    'order_id' => $order_id,
    'message' => 'Order created successfully',
    'total' => $total,
    'user' => $user['Name'],
    'redirect' => '/order-confirmation'
)));
?>