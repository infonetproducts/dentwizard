<?php
// api/v1/giftcard/purchase.php
// PHP 5.6 Compatible - Purchase a gift card

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = array('amount', 'recipient_email', 'from_name');
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => "Field '$field' is required"
        ));
        exit;
    }
}

$amount = (float)$input['amount'];
$recipient_email = filter_var($input['recipient_email'], FILTER_VALIDATE_EMAIL);
$from_name = $input['from_name'];
$from_email = isset($input['from_email']) ? filter_var($input['from_email'], FILTER_VALIDATE_EMAIL) : '';
$message = isset($input['message']) ? $input['message'] : '';
$delivery_date = isset($input['delivery_date']) ? $input['delivery_date'] : date('Y-m-d');

// Validate amount
if ($amount < 10 || $amount > 500) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Gift card amount must be between $10 and $500'
    ));
    exit;
}

// Validate email
if (!$recipient_email) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Invalid recipient email address'
    ));
    exit;
}

// Generate unique gift card code
function generateGiftCardCode() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < 16; $i++) {
        if ($i > 0 && $i % 4 == 0) {
            $code .= '-';
        }
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Optional authentication
$user_id = null;
$client_id = null;
if (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_GET['token'])) {
    try {
        AuthMiddleware::validateRequest();
        $user_id = $GLOBALS['auth_user']['id'];
        $client_id = $GLOBALS['auth_user']['client_id'];
    } catch (Exception $e) {
        // Continue without auth - guest purchase
    }
}

$pdo = getPDOConnection();

try {
    // Generate unique code
    $max_attempts = 10;
    $code = '';
    for ($i = 0; $i < $max_attempts; $i++) {
        $code = generateGiftCardCode();
        
        // Check if code already exists
        $stmt = $pdo->prepare("SELECT id FROM gift_cards WHERE code = :code");
        $stmt->execute(array('code' => $code));
        if (!$stmt->fetch()) {
            break; // Code is unique
        }
        
        if ($i == $max_attempts - 1) {
            throw new Exception('Failed to generate unique code');
        }
    }
    
    // Calculate expiry (1 year from purchase)
    $expiry_date = date('Y-m-d', strtotime('+1 year'));
    
    // Insert gift card
    $stmt = $pdo->prepare("
        INSERT INTO gift_cards (
            code,
            original_amount,
            current_balance,
            status,
            recipient_email,
            from_name,
            from_email,
            message,
            delivery_date,
            created_date,
            expiry_date,
            purchased_by_user_id,
            client_id
        ) VALUES (
            :code,
            :amount,
            :amount,
            'active',
            :recipient_email,
            :from_name,
            :from_email,
            :message,
            :delivery_date,
            NOW(),
            :expiry_date,
            :user_id,
            :client_id
        )
    ");
    
    $stmt->execute(array(
        'code' => $code,
        'amount' => $amount,
        'recipient_email' => $recipient_email,
        'from_name' => $from_name,
        'from_email' => $from_email,
        'message' => $message,
        'delivery_date' => $delivery_date,
        'expiry_date' => $expiry_date,
        'user_id' => $user_id,
        'client_id' => $client_id
    ));
    
    $gift_card_id = $pdo->lastInsertId();
    
    // Send email notification (would integrate with email service)
    // For now, just return the details
    
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'gift_card_id' => $gift_card_id,
            'code' => $code,
            'amount' => $amount,
            'recipient_email' => $recipient_email,
            'from_name' => $from_name,
            'message' => $message,
            'delivery_date' => $delivery_date,
            'expiry_date' => $expiry_date,
            'status' => 'active',
            'email_sent' => false, // Would be true when email service is integrated
            'message_to_buyer' => 'Gift card purchased successfully. The recipient will receive it on ' . $delivery_date
        )
    ));
    
} catch (Exception $e) {
    error_log('Gift card purchase error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to purchase gift card'
    ));
}
?>