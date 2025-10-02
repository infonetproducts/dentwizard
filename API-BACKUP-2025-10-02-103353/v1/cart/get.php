<?php
// api/v1/cart/get.php
// PHP 5.6 Compatible - Get cart with budget information

// CORS Headers - MUST be at the very top
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';

// Start session for cart
if (session_id() === '') {
    session_start();
}

// Optional authentication
$user_id = null;
$budget_info = null;

if (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_GET['token'])) {
    try {
        AuthMiddleware::validateRequest();
        $user_id = $GLOBALS['auth_user']['id'];
    } catch (Exception $e) {
        // Continue without auth
    }
}

// Get cart from session or database
$cart_items = array();
$cart_total = 0;
$total_items = 0;

if ($user_id) {
    // Get cart from database for logged-in user
    $pdo = getPDOConnection();
    $base_url = getBaseUrl();
    
    $stmt = $pdo->prepare("
        SELECT cart_data FROM UserCarts WHERE UID = :user_id
    ");
    $stmt->execute(array('user_id' => $user_id));
    $cart_data = $stmt->fetch();
    
    if ($cart_data && $cart_data['cart_data']) {
        $cart_items = json_decode($cart_data['cart_data'], true);
    }
    
    // Get user's budget information
    $stmt = $pdo->prepare("
        SELECT Budget, BudgetBalance 
        FROM Users 
        WHERE ID = :user_id
    ");
    $stmt->execute(array('user_id' => $user_id));
    $budget = $stmt->fetch();
    
    if ($budget && $budget['Budget'] !== null) {
        $budget_limit = (float)$budget['Budget'];
        $budget_balance = (float)$budget['BudgetBalance'];
        $budget_used = $budget_limit - $budget_balance;
        $budget_percentage = $budget_limit > 0 ? round(($budget_used / $budget_limit) * 100, 0) : 100;
        
        $budget_info = array(
            'has_budget' => true,
            'budget_limit' => $budget_limit,
            'budget_balance' => $budget_balance,
            'budget_used' => $budget_used,
            'percentage_used' => $budget_percentage,
            'display_text' => '$' . number_format($budget_balance, 2) . ' / $' . number_format($budget_limit, 2)
        );
    }
} elseif (isset($_SESSION['cart'])) {
    // Get cart from session for guest
    $cart_items = $_SESSION['cart'];
}

// Calculate totals
foreach ($cart_items as $item) {
    $cart_total += isset($item['total_price']) ? $item['total_price'] : ($item['unit_price'] * $item['quantity']);
    $total_items += $item['quantity'];
}

// Check if cart fits in budget (if user has budget)
$budget_status = array(
    'within_budget' => true,
    'can_checkout' => true,
    'message' => ''
);

if ($budget_info && $budget_info['has_budget']) {
    if ($cart_total > $budget_info['budget_balance']) {
        $budget_status['within_budget'] = false;
        $budget_status['can_checkout'] = false;
        $shortage = $cart_total - $budget_info['budget_balance'];
        $budget_status['message'] = 'Cart exceeds budget by $' . number_format($shortage, 2);
        $budget_status['shortage'] = $shortage;
    } else {
        $budget_status['balance_after'] = $budget_info['budget_balance'] - $cart_total;
        $budget_status['message'] = 'Budget after order: $' . number_format($budget_status['balance_after'], 2);
    }
}

// Response
$response = array(
    'success' => true,
    'data' => array(
        'cart_items' => array_values($cart_items),
        'cart_summary' => array(
            'total_items' => $total_items,
            'unique_items' => count($cart_items),
            'subtotal' => $cart_total,
            'tax' => 0, // Calculate based on location
            'shipping' => 0, // Calculate based on location
            'total' => $cart_total
        ),
        'budget' => $budget_info ? $budget_info : array('has_budget' => false),
        'budget_status' => $budget_status,
        'user_authenticated' => ($user_id !== null)
    )
);

echo json_encode($response);
?>