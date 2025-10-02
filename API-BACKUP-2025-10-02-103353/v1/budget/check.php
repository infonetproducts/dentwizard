<?php
// api/v1/budget/check.php
// PHP 5.6 Compatible - Check if order fits within budget

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Require authentication
AuthMiddleware::validateRequest();
$user_id = $GLOBALS['auth_user']['id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$order_total = isset($input['order_total']) ? (float)$input['order_total'] : 0;
$include_shipping = isset($input['include_shipping']) ? $input['include_shipping'] : true;
$shipping_cost = isset($input['shipping_cost']) ? (float)$input['shipping_cost'] : 0;

if ($include_shipping) {
    $order_total += $shipping_cost;
}

$pdo = getPDOConnection();

try {
    // Get user's budget info
    $stmt = $pdo->prepare("
        SELECT Budget, BudgetBalance 
        FROM Users 
        WHERE ID = :user_id
    ");
    $stmt->execute(array('user_id' => $user_id));
    $budget = $stmt->fetch();
    
    // If no budget set, allow order
    if (!$budget || $budget['Budget'] === null) {
        echo json_encode(array(
            'success' => true,
            'data' => array(
                'has_budget' => false,
                'can_proceed' => true,
                'message' => 'No budget restrictions apply'
            )
        ));
        exit;
    }
    
    $budget_limit = (float)$budget['Budget'];
    $budget_balance = (float)$budget['BudgetBalance'];
    
    // Check if order exceeds available budget
    $can_proceed = $order_total <= $budget_balance;
    $balance_after = $budget_balance - $order_total;
    
    $response = array(
        'success' => true,
        'data' => array(
            'has_budget' => true,
            'can_proceed' => $can_proceed,
            'budget_limit' => $budget_limit,
            'budget_balance' => $budget_balance,
            'order_total' => $order_total,
            'balance_after_order' => $balance_after,
            'shortage' => $can_proceed ? 0 : ($order_total - $budget_balance),
            'message' => $can_proceed 
                ? 'Order within budget' 
                : 'Order exceeds available budget by $' . number_format($order_total - $budget_balance, 2)
        )
    );
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Budget check error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to check budget'
    ));
}
?>