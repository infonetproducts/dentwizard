<?php
// Tax Calculation API - PHP 5.3+ compatible version
require_once __DIR__ . '/../../common/cors.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['to_state']) || !isset($input['to_zip']) || !isset($input['amount'])) {
    die(json_encode(array('status' => 'error', 'message' => 'Missing required fields')));
}

$to_state = $input['to_state'];
$to_zip = $input['to_zip'];
$to_city = isset($input['to_city']) ? $input['to_city'] : '';
$amount = floatval($input['amount']);
$shipping = isset($input['shipping']) ? floatval($input['shipping']) : 0;
$line_items = isset($input['line_items']) ? $input['line_items'] : array();

// State tax rates (using old array syntax)
$stateTaxRates = array(
    'AL' => 0.04, 'AK' => 0.00, 'AZ' => 0.056, 'AR' => 0.065,
    'CA' => 0.0725, 'CO' => 0.029, 'CT' => 0.0635, 'DE' => 0.00,
    'FL' => 0.06, 'GA' => 0.04, 'HI' => 0.04, 'ID' => 0.06,
    'IL' => 0.0625, 'IN' => 0.07, 'IA' => 0.06, 'KS' => 0.065,
    'KY' => 0.06, 'LA' => 0.0445, 'ME' => 0.055, 'MD' => 0.06,
    'MA' => 0.0625, 'MI' => 0.06, 'MN' => 0.06875, 'MS' => 0.07,
    'MO' => 0.04225, 'MT' => 0.00, 'NE' => 0.055, 'NV' => 0.0685,
    'NH' => 0.00, 'NJ' => 0.06625, 'NM' => 0.05125, 'NY' => 0.04,
    'NC' => 0.0475, 'ND' => 0.05, 'OH' => 0.0575, 'OK' => 0.045,
    'OR' => 0.00, 'PA' => 0.06, 'RI' => 0.07, 'SC' => 0.06,
    'SD' => 0.045, 'TN' => 0.07, 'TX' => 0.0625, 'UT' => 0.0485,
    'VT' => 0.06, 'VA' => 0.043, 'WA' => 0.065, 'WV' => 0.06,
    'WI' => 0.05, 'WY' => 0.04, 'DC' => 0.06
);

// Check for clothing exemption (PA doesn't tax apparel)
$isClothingExempt = false;
if ($to_state === 'PA' && !empty($line_items)) {
    // Check if all items are clothing (tax_code 20010)
    $allClothing = true;
    foreach ($line_items as $item) {
        $taxCode = isset($item['product_tax_code']) ? $item['product_tax_code'] : 
                   (isset($item['tax_code']) ? $item['tax_code'] : '');
        if ($taxCode !== '20010') {
            $allClothing = false;
            break;
        }
    }
    if ($allClothing) {
        $isClothingExempt = true;
    }
}

// Calculate tax
if ($isClothingExempt) {
    // PA clothing exemption
    $taxRate = 0;
    $tax = 0;
    $taxableAmount = 0;
} else {
    $taxRate = isset($stateTaxRates[$to_state]) ? $stateTaxRates[$to_state] : 0.06;
    $taxableAmount = $amount + $shipping;
    $tax = $taxableAmount * $taxRate;
}

// Return response
echo json_encode(array(
    'status' => 'success',
    'tax' => $tax,
    'rate' => $taxRate,
    'taxable_amount' => $taxableAmount,
    'tax_source' => 'calculated',
    'clothing_exempt' => $isClothingExempt,
    'state' => $to_state
));
?>