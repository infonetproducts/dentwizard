<?php
// api/v1/checkout/submit.php
// PHP 5.6 Compatible - Submit order with budget deduction

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Require authentication
AuthMiddleware::validateRequest();
$user_id = $GLOBALS['auth_user']['id'];
$client_id = $GLOBALS['auth_user']['client_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = array('shipping_address', 'items');
foreach ($required as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => "Missing required field: $field"
        ));
        exit;
    }
}

$pdo = getPDOConnection();

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Get user's budget information
    $stmt = $pdo->prepare("SELECT Budget, BudgetBalance, Email, Name FROM Users WHERE ID = :user_id");
    $stmt->execute(array('user_id' => $user_id));
    $user = $stmt->fetch();
    
    // Calculate order total
    $subtotal = 0;
    $items = $input['items'];
    foreach ($items as $item) {
        $item_total = $item['quantity'] * $item['unit_price'];
        $subtotal += $item_total;
    }
    
    // Add shipping and tax
    $shipping = isset($input['shipping_cost']) ? (float)$input['shipping_cost'] : 0;
    $tax = isset($input['tax']) ? (float)$input['tax'] : 0;
    $total = $subtotal + $shipping + $tax;
    
    // Check budget if user has one
    $has_budget = ($user['Budget'] !== null);
    if ($has_budget) {
        $budget_limit = (float)$user['Budget'];
        $budget_balance = (float)$user['BudgetBalance'];
        
        if ($total > $budget_balance) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(array(
                'success' => false,
                'error' => 'Order exceeds available budget',
                'data' => array(
                    'order_total' => $total,
                    'budget_balance' => $budget_balance,
                    'shortage' => $total - $budget_balance
                )
            ));
            exit;
        }
    }
    
    // Create order number
    $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO Orders (
            UID, CID, OrderNumber, OrderDate, Status, 
            Subtotal, ShippingCost, Tax, Total,
            ShipToName, ShipToAddress1, ShipToAddress2, 
            ShipToCity, ShipToState, ShipToZip,
            PaymentMethod, CreatedDate
        ) VALUES (
            :user_id, :client_id, :order_number, NOW(), 'pending',
            :subtotal, :shipping, :tax, :total,
            :ship_name, :ship_address1, :ship_address2,
            :ship_city, :ship_state, :ship_zip,
            :payment_method, NOW()
        )
    ");
    
    $ship = $input['shipping_address'];
    $stmt->execute(array(
        'user_id' => $user_id,
        'client_id' => $client_id,
        'order_number' => $order_number,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'ship_name' => isset($ship['name']) ? $ship['name'] : $user['Name'],
        'ship_address1' => $ship['address1'],
        'ship_address2' => isset($ship['address2']) ? $ship['address2'] : '',
        'ship_city' => $ship['city'],
        'ship_state' => $ship['state'],
        'ship_zip' => $ship['zip'],
        'payment_method' => isset($input['payment_method']) ? $input['payment_method'] : 'account'
    ));
    
    $order_id = $pdo->lastInsertId();
    
    // Insert order items
    foreach ($items as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO OrderItems (
                OrderRecordID, ItemID, ItemTitle, 
                Quantity, Price, Total,
                Size, Color, CustomName, CustomNumber, Logo
            ) VALUES (
                :order_id, :item_id, :item_title,
                :quantity, :price, :total,
                :size, :color, :custom_name, :custom_number, :logo
            )
        ");
        
        $item_total = $item['quantity'] * $item['unit_price'];
        $stmt->execute(array(
            'order_id' => $order_id,
            'item_id' => $item['product_id'],
            'item_title' => $item['product_name'],
            'quantity' => $item['quantity'],
            'price' => $item['unit_price'],
            'total' => $item_total,
            'size' => isset($item['size']) ? $item['size'] : null,
            'color' => isset($item['color']) ? $item['color'] : null,
            'custom_name' => isset($item['custom_name']) ? $item['custom_name'] : null,
            'custom_number' => isset($item['custom_number']) ? $item['custom_number'] : null,
            'logo' => isset($item['logo_option']) ? $item['logo_option'] : null
        ));
    }
    
    // Deduct from budget if applicable
    if ($has_budget) {
        $new_balance = $budget_balance - $total;
        
        // Update user's budget balance
        $stmt = $pdo->prepare("
            UPDATE Users 
            SET BudgetBalance = :new_balance 
            WHERE ID = :user_id
        ");
        $stmt->execute(array(
            'new_balance' => $new_balance,
            'user_id' => $user_id
        ));
        
        // Log budget transaction
        $stmt = $pdo->prepare("
            INSERT INTO budget_log_all_trans (
                cid, user_id, action_title, log_type, id, created_dtm
            ) VALUES (
                :client_id, :user_id, 'Order Placed', 'order_placed', :order_id, NOW()
            )
        ");
        $stmt->execute(array(
            'client_id' => $client_id,
            'user_id' => $user_id,
            'order_id' => $order_id
        ));
        $log_id = $pdo->lastInsertId();
        
        // Log budget details
        $stmt = $pdo->prepare("
            INSERT INTO budget_log_all_trans_detail (
                log_id, field_name, old_value, new_value, created_dtm
            ) VALUES (
                :log_id, 'BudgetBalance', :old_value, :new_value, NOW()
            )
        ");
        $stmt->execute(array(
            'log_id' => $log_id,
            'old_value' => $budget_balance,
            'new_value' => $new_balance
        ));
    }
    
    // Clear user's cart
    $stmt = $pdo->prepare("DELETE FROM UserCarts WHERE UID = :user_id");
    $stmt->execute(array('user_id' => $user_id));
    
    // Clear session cart
    if (session_id() === '') {
        session_start();
    }
    unset($_SESSION['cart']);
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total' => $total,
            'status' => 'pending',
            'budget_deducted' => $has_budget,
            'new_budget_balance' => $has_budget ? $new_balance : null,
            'message' => 'Order placed successfully'
        )
    ));
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Checkout error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to process order'
    ));
}
?>