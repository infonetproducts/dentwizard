<?php
// api/v1/cart/apply-discount.php
// PHP 5.6 Compatible - Apply gift card, promo code, or dealer code to cart

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Start session
if (session_id() === '') {
    session_start();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['discount_type'])) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Discount type is required'
    ));
    exit;
}

$discount_type = $input['discount_type']; // 'promo_code', 'gift_card', 'dealer_code'
$code = isset($input['code']) ? trim($input['code']) : '';
$cart_total = isset($input['cart_total']) ? (float)$input['cart_total'] : 0;

// Optional authentication
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
    $discount_data = array();
    
    switch ($discount_type) {
        case 'promo_code':
            // Validate promo code
            $stmt = $pdo->prepare("
                SELECT * FROM promo_codes 
                WHERE code = :code AND status = 'active'
            ");
            $stmt->execute(array('code' => strtoupper($code)));
            $promo = $stmt->fetch();
            
            if (!$promo) {
                throw new Exception('Invalid promo code');
            }
            
            // Calculate discount
            if ($promo['discount_type'] === 'percentage') {
                $discount_amount = ($cart_total * $promo['discount_value']) / 100;
                if ($promo['maximum_discount'] && $discount_amount > $promo['maximum_discount']) {
                    $discount_amount = $promo['maximum_discount'];
                }
            } else {
                $discount_amount = $promo['discount_value'];
            }
            
            // Store in session
            $_SESSION['promo_code_str'] = $code;
            $_SESSION['set_promo_code_discount'] = $discount_amount;
            $_SESSION['total_price_after_promo_code'] = $cart_total - $discount_amount;
            
            $discount_data = array(
                'type' => 'promo_code',
                'code' => $code,
                'discount_amount' => $discount_amount,
                'discount_percentage' => $promo['discount_type'] === 'percentage' ? $promo['discount_value'] : 0,
                'description' => $promo['description'],
                'free_shipping' => $promo['discount_type'] === 'free_shipping'
            );
            break;
            
        case 'gift_card':
            // Validate gift card
            $stmt = $pdo->prepare("
                SELECT * FROM gift_cards 
                WHERE code = :code AND status = 'active'
            ");
            $stmt->execute(array('code' => strtoupper($code)));
            $gift_card = $stmt->fetch();
            
            if (!$gift_card) {
                throw new Exception('Invalid gift card');
            }
            
            if ($gift_card['current_balance'] <= 0) {
                throw new Exception('Gift card has no balance');
            }
            
            // Calculate how much to use
            $discount_amount = min($gift_card['current_balance'], $cart_total);
            
            // Store in session
            $_SESSION['gift_card_code'] = $code;
            $_SESSION['gift_discount_amount'] = $discount_amount;
            $_SESSION['gift_card_id'] = $gift_card['id'];
            
            $discount_data = array(
                'type' => 'gift_card',
                'code' => $code,
                'discount_amount' => $discount_amount,
                'available_balance' => $gift_card['current_balance'],
                'balance_after' => $gift_card['current_balance'] - $discount_amount
            );
            break;
            
        case 'dealer_code':
            // Validate dealer code (for B2B customers)
            $stmt = $pdo->prepare("
                SELECT * FROM dealer_codes 
                WHERE code = :code AND status = 'active'
            ");
            $stmt->execute(array('code' => strtoupper($code)));
            $dealer = $stmt->fetch();
            
            if (!$dealer) {
                throw new Exception('Invalid dealer code');
            }
            
            // Calculate dealer discount
            $discount_amount = ($cart_total * $dealer['discount_percentage']) / 100;
            
            // Store in session
            $_SESSION['get_dealer_code_balance'] = $dealer['available_balance'];
            $_SESSION['set_dealer_discount'] = $discount_amount;
            $_SESSION['dealer_code'] = $code;
            
            $discount_data = array(
                'type' => 'dealer_code',
                'code' => $code,
                'discount_amount' => $discount_amount,
                'discount_percentage' => $dealer['discount_percentage'],
                'dealer_name' => $dealer['dealer_name']
            );
            break;
            
        default:
            throw new Exception('Invalid discount type');
    }
    
    // Calculate new totals
    $new_total = $cart_total - $discount_data['discount_amount'];
    if ($new_total < 0) $new_total = 0;
    
    // Get all active discounts from session
    $active_discounts = array();
    if (isset($_SESSION['promo_code_str'])) {
        $active_discounts[] = array(
            'type' => 'promo_code',
            'code' => $_SESSION['promo_code_str'],
            'amount' => $_SESSION['set_promo_code_discount']
        );
    }
    if (isset($_SESSION['gift_card_code'])) {
        $active_discounts[] = array(
            'type' => 'gift_card',
            'code' => $_SESSION['gift_card_code'],
            'amount' => $_SESSION['gift_discount_amount']
        );
    }
    if (isset($_SESSION['dealer_code'])) {
        $active_discounts[] = array(
            'type' => 'dealer_code',
            'code' => $_SESSION['dealer_code'],
            'amount' => $_SESSION['set_dealer_discount']
        );
    }
    
    // Calculate total discount
    $total_discount = 0;
    foreach ($active_discounts as $discount) {
        $total_discount += $discount['amount'];
    }
    
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'discount_applied' => $discount_data,
            'active_discounts' => $active_discounts,
            'original_total' => $cart_total,
            'total_discount' => $total_discount,
            'final_total' => $cart_total - $total_discount,
            'message' => 'Discount applied successfully'
        )
    ));
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage()
    ));
}
?>