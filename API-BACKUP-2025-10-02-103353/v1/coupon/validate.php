<?php
// api/v1/coupon/validate.php
// PHP 5.6 Compatible - Validate promo codes and coupons

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['promo_code'])) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Promo code is required'
    ));
    exit;
}

$promo_code = strtoupper(trim($input['promo_code']));
$order_total = isset($input['order_total']) ? (float)$input['order_total'] : 0;

// Optional user authentication for user-specific coupons
$user_id = null;
$client_id = null;
if (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_GET['token'])) {
    try {
        AuthMiddleware::validateRequest();
        $user_id = $GLOBALS['auth_user']['id'];
        $client_id = $GLOBALS['auth_user']['client_id'];
    } catch (Exception $e) {
        // Continue without auth
    }
}

$pdo = getPDOConnection();

try {
    // Check if promo code exists and is valid
    $stmt = $pdo->prepare("
        SELECT 
            pc.id,
            pc.code,
            pc.description,
            pc.discount_type,
            pc.discount_value,
            pc.minimum_order_value,
            pc.maximum_discount,
            pc.usage_limit,
            pc.usage_count,
            pc.user_limit_per_customer,
            pc.start_date,
            pc.end_date,
            pc.status,
            pc.client_id,
            pc.user_specific,
            pc.allowed_users,
            pc.category_specific,
            pc.allowed_categories
        FROM promo_codes pc
        WHERE pc.code = :code
        AND pc.status = 'active'
    ");
    $stmt->execute(array('code' => $promo_code));
    $promo = $stmt->fetch();
    
    if (!$promo) {
        http_response_code(404);
        echo json_encode(array(
            'success' => false,
            'error' => 'Invalid promo code'
        ));
        exit;
    }
    
    // Check dates
    $now = time();
    if ($promo['start_date'] && strtotime($promo['start_date']) > $now) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Promo code is not yet active',
            'start_date' => $promo['start_date']
        ));
        exit;
    }
    
    if ($promo['end_date'] && strtotime($promo['end_date']) < $now) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Promo code has expired',
            'end_date' => $promo['end_date']
        ));
        exit;
    }
    
    // Check usage limit
    if ($promo['usage_limit'] && $promo['usage_count'] >= $promo['usage_limit']) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Promo code usage limit reached'
        ));
        exit;
    }
    
    // Check user-specific restrictions
    if ($promo['user_specific'] && $promo['allowed_users']) {
        if (!$user_id) {
            http_response_code(400);
            echo json_encode(array(
                'success' => false,
                'error' => 'This promo code requires login'
            ));
            exit;
        }
        
        $allowed_users = explode(',', $promo['allowed_users']);
        if (!in_array($user_id, $allowed_users)) {
            http_response_code(400);
            echo json_encode(array(
                'success' => false,
                'error' => 'This promo code is not available for your account'
            ));
            exit;
        }
    }
    
    // Check user usage limit
    if ($user_id && $promo['user_limit_per_customer']) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as usage_count
            FROM promo_code_usage
            WHERE promo_code_id = :promo_id
            AND user_id = :user_id
        ");
        $stmt->execute(array(
            'promo_id' => $promo['id'],
            'user_id' => $user_id
        ));
        $user_usage = $stmt->fetch();
        
        if ($user_usage['usage_count'] >= $promo['user_limit_per_customer']) {
            http_response_code(400);
            echo json_encode(array(
                'success' => false,
                'error' => 'You have already used this promo code the maximum number of times'
            ));
            exit;
        }
    }
    
    // Check minimum order value
    if ($promo['minimum_order_value'] && $order_total < $promo['minimum_order_value']) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Minimum order value of $' . number_format($promo['minimum_order_value'], 2) . ' required',
            'minimum_required' => (float)$promo['minimum_order_value'],
            'current_total' => $order_total
        ));
        exit;
    }
    
    // Calculate discount
    $discount_amount = 0;
    $discount_percentage = 0;
    
    if ($promo['discount_type'] === 'percentage') {
        $discount_percentage = (float)$promo['discount_value'];
        $discount_amount = ($order_total * $discount_percentage) / 100;
        
        // Apply maximum discount cap if set
        if ($promo['maximum_discount'] && $discount_amount > $promo['maximum_discount']) {
            $discount_amount = (float)$promo['maximum_discount'];
        }
    } elseif ($promo['discount_type'] === 'fixed') {
        $discount_amount = (float)$promo['discount_value'];
        
        // Can't discount more than the order total
        if ($discount_amount > $order_total) {
            $discount_amount = $order_total;
        }
    } elseif ($promo['discount_type'] === 'free_shipping') {
        // Free shipping flag
        $discount_amount = 0; // Will be calculated based on shipping cost
    }
    
    // Return promo details
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'promo_id' => (int)$promo['id'],
            'code' => $promo['code'],
            'description' => $promo['description'],
            'discount_type' => $promo['discount_type'],
            'discount_value' => (float)$promo['discount_value'],
            'discount_amount' => $discount_amount,
            'discount_percentage' => $discount_percentage,
            'minimum_order_value' => (float)$promo['minimum_order_value'],
            'maximum_discount' => (float)$promo['maximum_discount'],
            'free_shipping' => $promo['discount_type'] === 'free_shipping',
            'can_apply' => true,
            'message' => $promo['discount_type'] === 'percentage' 
                ? $discount_percentage . '% off' 
                : ($promo['discount_type'] === 'free_shipping' 
                    ? 'Free shipping' 
                    : '$' . number_format($discount_amount, 2) . ' off')
        )
    ));
    
} catch (Exception $e) {
    error_log('Promo code validation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to validate promo code'
    ));
}
?>