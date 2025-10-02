<?php
// api/v1/giftcard/validate.php
// PHP 5.6 Compatible - Validate and check gift card balance

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['gift_card_code'])) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Gift card code is required'
    ));
    exit;
}

$gift_card_code = trim($input['gift_card_code']);

// Validate format (typically alphanumeric, 12-16 characters)
if (!preg_match('/^[A-Z0-9]{12,16}$/', $gift_card_code)) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Invalid gift card format'
    ));
    exit;
}

$pdo = getPDOConnection();

try {
    // Check if gift card exists and is valid
    $stmt = $pdo->prepare("
        SELECT 
            gc.id,
            gc.code,
            gc.original_amount,
            gc.current_balance,
            gc.status,
            gc.created_date,
            gc.expiry_date,
            gc.recipient_email,
            gc.from_name,
            gc.message,
            gc.delivery_date
        FROM gift_cards gc
        WHERE gc.code = :code
        AND gc.status = 'active'
    ");
    $stmt->execute(array('code' => $gift_card_code));
    $gift_card = $stmt->fetch();
    
    if (!$gift_card) {
        http_response_code(404);
        echo json_encode(array(
            'success' => false,
            'error' => 'Gift card not found or inactive'
        ));
        exit;
    }
    
    // Check if expired
    if ($gift_card['expiry_date'] && strtotime($gift_card['expiry_date']) < time()) {
        // Update status to expired
        $stmt = $pdo->prepare("UPDATE gift_cards SET status = 'expired' WHERE id = :id");
        $stmt->execute(array('id' => $gift_card['id']));
        
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Gift card has expired',
            'expiry_date' => $gift_card['expiry_date']
        ));
        exit;
    }
    
    // Check if balance is available
    if ($gift_card['current_balance'] <= 0) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Gift card has no remaining balance'
        ));
        exit;
    }
    
    // Get usage history
    $stmt = $pdo->prepare("
        SELECT 
            order_id,
            amount_used,
            used_date
        FROM gift_card_usage
        WHERE gift_card_id = :gift_card_id
        ORDER BY used_date DESC
        LIMIT 5
    ");
    $stmt->execute(array('gift_card_id' => $gift_card['id']));
    $usage_history = $stmt->fetchAll();
    
    // Return gift card details
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'gift_card_id' => (int)$gift_card['id'],
            'code' => $gift_card['code'],
            'original_amount' => (float)$gift_card['original_amount'],
            'current_balance' => (float)$gift_card['current_balance'],
            'amount_used' => (float)($gift_card['original_amount'] - $gift_card['current_balance']),
            'status' => $gift_card['status'],
            'created_date' => $gift_card['created_date'],
            'expiry_date' => $gift_card['expiry_date'],
            'from_name' => $gift_card['from_name'],
            'message' => $gift_card['message'],
            'usage_history' => $usage_history,
            'can_apply' => true
        )
    ));
    
} catch (Exception $e) {
    error_log('Gift card validation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to validate gift card'
    ));
}
?>