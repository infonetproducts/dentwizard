<?php
// api/v1/budget/status.php
// PHP 5.6 Compatible - Quick budget status for header display
// Lightweight endpoint to show budget throughout the site

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Check authentication - but allow guest access
$user_id = null;
$has_auth = false;

// Check for authentication without requiring it
if (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_GET['token']) || isset($_SESSION['user_id'])) {
    try {
        AuthMiddleware::validateRequest();
        $user_id = $GLOBALS['auth_user']['id'];
        $has_auth = true;
    } catch (Exception $e) {
        // Not authenticated - that's OK
    }
}

// If not authenticated, return no budget
if (!$has_auth || !$user_id) {
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'authenticated' => false,
            'has_budget' => false,
            'display_text' => '',
            'show_budget' => false
        )
    ));
    exit;
}

$pdo = getPDOConnection();

try {
    // Get budget info with minimal query
    $stmt = $pdo->prepare("
        SELECT 
            Budget,
            BudgetBalance,
            Name
        FROM Users 
        WHERE ID = :user_id
    ");
    $stmt->execute(array('user_id' => $user_id));
    $user = $stmt->fetch();
    
    if (!$user || $user['Budget'] === null) {
        // User has no budget
        echo json_encode(array(
            'success' => true,
            'data' => array(
                'authenticated' => true,
                'has_budget' => false,
                'display_text' => 'No Budget',
                'show_budget' => false,
                'user_name' => $user['Name']
            )
        ));
        exit;
    }
    
    $budget = (float)$user['Budget'];
    $balance = (float)$user['BudgetBalance'];
    $used = $budget - $balance;
    $percentage = $budget > 0 ? round(($used / $budget) * 100, 0) : 100;
    
    // Determine color/status
    $status = 'good'; // green
    if ($percentage >= 90) {
        $status = 'critical'; // red
    } elseif ($percentage >= 75) {
        $status = 'warning'; // yellow
    }
    
    // Format display text
    $display_text = '$' . number_format($balance, 2) . ' / $' . number_format($budget, 2);
    $display_short = '$' . number_format($balance, 0);
    
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'authenticated' => true,
            'has_budget' => true,
            'show_budget' => true,
            'budget_limit' => $budget,
            'budget_balance' => $balance,
            'budget_used' => $used,
            'percentage_used' => $percentage,
            'status' => $status,
            'display_text' => $display_text,
            'display_short' => $display_short,
            'display_balance' => '$' . number_format($balance, 2),
            'user_name' => $user['Name'],
            'can_order' => $balance > 0
        )
    ));
    
} catch (Exception $e) {
    error_log('Budget status error: ' . $e->getMessage());
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'authenticated' => $has_auth,
            'has_budget' => false,
            'show_budget' => false,
            'error' => true
        )
    ));
}
?>