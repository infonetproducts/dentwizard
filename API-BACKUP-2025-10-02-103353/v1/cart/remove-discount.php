<?php
// api/v1/cart/remove-discount.php
// PHP 5.6 Compatible - Remove a discount from cart

require_once '../../config/cors.php';
require_once '../../config/database.php';

// Start session
if (session_id() === '') {
    session_start();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$discount_type = isset($input['discount_type']) ? $input['discount_type'] : 'all';

// Remove specific or all discounts
switch ($discount_type) {
    case 'promo_code':
        unset($_SESSION['promo_code_str']);
        unset($_SESSION['set_promo_code_discount']);
        unset($_SESSION['total_price_after_promo_code']);
        $message = 'Promo code removed';
        break;
        
    case 'gift_card':
        unset($_SESSION['gift_card_code']);
        unset($_SESSION['gift_discount_amount']);
        unset($_SESSION['gift_card_id']);
        unset($_SESSION['gift_card_is_gift_card_item']);
        unset($_SESSION['gift_card_gift_price']);
        $message = 'Gift card removed';
        break;
        
    case 'dealer_code':
        unset($_SESSION['get_dealer_code_balance']);
        unset($_SESSION['set_dealer_discount']);
        unset($_SESSION['dealer_code']);
        $message = 'Dealer code removed';
        break;
        
    case 'all':
    default:
        // Remove all discount-related session variables
        unset($_SESSION['promo_code_str']);
        unset($_SESSION['set_promo_code_discount']);
        unset($_SESSION['total_price_after_promo_code']);
        unset($_SESSION['gift_card_code']);
        unset($_SESSION['gift_discount_amount']);
        unset($_SESSION['gift_card_id']);
        unset($_SESSION['gift_card_is_gift_card_item']);
        unset($_SESSION['gift_card_gift_price']);
        unset($_SESSION['get_dealer_code_balance']);
        unset($_SESSION['set_dealer_discount']);
        unset($_SESSION['dealer_code']);
        unset($_SESSION['sale_discount_total']);
        $message = 'All discounts removed';
        break;
}

// Get remaining active discounts
$active_discounts = array();
$total_discount = 0;

if (isset($_SESSION['promo_code_str'])) {
    $active_discounts[] = array(
        'type' => 'promo_code',
        'code' => $_SESSION['promo_code_str'],
        'amount' => $_SESSION['set_promo_code_discount']
    );
    $total_discount += $_SESSION['set_promo_code_discount'];
}

if (isset($_SESSION['gift_card_code'])) {
    $active_discounts[] = array(
        'type' => 'gift_card',
        'code' => $_SESSION['gift_card_code'],
        'amount' => $_SESSION['gift_discount_amount']
    );
    $total_discount += $_SESSION['gift_discount_amount'];
}

if (isset($_SESSION['dealer_code'])) {
    $active_discounts[] = array(
        'type' => 'dealer_code',
        'code' => $_SESSION['dealer_code'],
        'amount' => $_SESSION['set_dealer_discount']
    );
    $total_discount += $_SESSION['set_dealer_discount'];
}

echo json_encode(array(
    'success' => true,
    'message' => $message,
    'data' => array(
        'active_discounts' => $active_discounts,
        'total_discount' => $total_discount
    )
));
?>