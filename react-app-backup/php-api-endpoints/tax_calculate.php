<?php
/**
 * Tax Calculation API Endpoint with TaxJar Integration
 * Deploy to: /lg/API/v1/tax/calculate.php
 */

session_start();
include_once("../../include/db.php");

// If TaxJar is available, include it
if (file_exists("../../taxjar/common_function_taxjar.php")) {
    include_once("../../taxjar/common_function_taxjar.php");
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['to_state']) || !isset($input['to_zip']) || !isset($input['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$to_state = $input['to_state'];
$to_zip = $input['to_zip'];
$to_city = $input['to_city'] ?? '';
$to_street = $input['to_street'] ?? '';
$amount = floatval($input['amount']);
$shipping = floatval($input['shipping'] ?? 0);
$line_items = $input['line_items'] ?? [];

// TaxJar API Key (should be in config)
$taxjar_api_key = 'YOUR_TAXJAR_API_KEY'; // Replace with actual key from config

try {
    // If TaxJar function is available, use it
    if (function_exists('calculateTaxJar')) {
        // Use existing TaxJar integration
        $taxData = calculateTaxJar([
            'to_country' => 'US',
            'to_zip' => $to_zip,
            'to_state' => $to_state,
            'to_city' => $to_city,
            'to_street' => $to_street,
            'amount' => $amount,
            'shipping' => $shipping,
            'line_items' => $line_items
        ]);
        
        echo json_encode([
            'tax' => $taxData['tax']['amount_to_collect'] ?? 0,
            'rate' => $taxData['tax']['rate'] ?? 0,
            'taxable_amount' => $taxData['tax']['taxable_amount'] ?? 0,
            'breakdown' => $taxData['tax']['breakdown'] ?? null
        ]);
    } else {
        // Direct TaxJar API call if function not available
        $curl = curl_init();
        
        $taxjar_data = [
            'to_country' => 'US',
            'to_zip' => $to_zip,
            'to_state' => $to_state,
            'to_city' => $to_city,
            'to_street' => $to_street,
            'amount' => $amount,
            'shipping' => $shipping,
            'line_items' => array_map(function($item, $index) {
                return [
                    'id' => (string)($item['id'] ?? $index),
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'product_tax_code' => $item['product_tax_code'] ?? ''
                ];
            }, $line_items, array_keys($line_items))
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.taxjar.com/v2/taxes",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($taxjar_data),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $taxjar_api_key,
                "Content-Type: application/json"
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if ($err) {
            throw new Exception("cURL Error: " . $err);
        }
        
        $taxjar_response = json_decode($response, true);
        
        if (isset($taxjar_response['tax'])) {
            echo json_encode([
                'tax' => $taxjar_response['tax']['amount_to_collect'] ?? 0,
                'rate' => $taxjar_response['tax']['rate'] ?? 0,
                'taxable_amount' => $taxjar_response['tax']['taxable_amount'] ?? 0,
                'breakdown' => $taxjar_response['tax']['breakdown'] ?? null,
                'freight_taxable' => $taxjar_response['tax']['freight_taxable'] ?? false,
                'has_nexus' => $taxjar_response['tax']['has_nexus'] ?? false,
                'tax_source' => 'taxjar'
            ]);
        } else {
            // Fallback to basic calculation if TaxJar fails
            throw new Exception("TaxJar API did not return tax data");
        }
    }
} catch (Exception $e) {
    // Fallback: Basic state tax calculation for all US states
    // These are approximate rates - TaxJar will give exact rates
    $stateTaxRates = [
        'AL' => 0.04,    // Alabama
        'AK' => 0.00,    // Alaska (no state tax)
        'AZ' => 0.056,   // Arizona
        'AR' => 0.065,   // Arkansas
        'CA' => 0.0725,  // California
        'CO' => 0.029,   // Colorado
        'CT' => 0.0635,  // Connecticut
        'DE' => 0.00,    // Delaware (no state tax)
        'FL' => 0.06,    // Florida
        'GA' => 0.04,    // Georgia
        'HI' => 0.04,    // Hawaii
        'ID' => 0.06,    // Idaho
        'IL' => 0.0625,  // Illinois
        'IN' => 0.07,    // Indiana
        'IA' => 0.06,    // Iowa
        'KS' => 0.065,   // Kansas
        'KY' => 0.06,    // Kentucky
        'LA' => 0.0445,  // Louisiana
        'ME' => 0.055,   // Maine
        'MD' => 0.06,    // Maryland
        'MA' => 0.0625,  // Massachusetts
        'MI' => 0.06,    // Michigan
        'MN' => 0.06875, // Minnesota
        'MS' => 0.07,    // Mississippi
        'MO' => 0.04225, // Missouri
        'MT' => 0.00,    // Montana (no state tax)
        'NE' => 0.055,   // Nebraska
        'NV' => 0.0685,  // Nevada
        'NH' => 0.00,    // New Hampshire (no state tax)
        'NJ' => 0.06625, // New Jersey
        'NM' => 0.05125, // New Mexico
        'NY' => 0.04,    // New York
        'NC' => 0.0475,  // North Carolina
        'ND' => 0.05,    // North Dakota
        'OH' => 0.0575,  // Ohio
        'OK' => 0.045,   // Oklahoma
        'OR' => 0.00,    // Oregon (no state tax)
        'PA' => 0.06,    // Pennsylvania
        'RI' => 0.07,    // Rhode Island
        'SC' => 0.06,    // South Carolina
        'SD' => 0.045,   // South Dakota
        'TN' => 0.07,    // Tennessee
        'TX' => 0.0625,  // Texas
        'UT' => 0.0485,  // Utah
        'VT' => 0.06,    // Vermont
        'VA' => 0.043,   // Virginia
        'WA' => 0.065,   // Washington
        'WV' => 0.06,    // West Virginia
        'WI' => 0.05,    // Wisconsin
        'WY' => 0.04,    // Wyoming
        'DC' => 0.06,    // Washington DC
    ];
    
    $taxRate = $stateTaxRates[$to_state] ?? 0;
    $taxableAmount = $amount + $shipping;
    
    echo json_encode([
        'tax' => $taxableAmount * $taxRate,
        'rate' => $taxRate,
        'taxable_amount' => $taxableAmount,
        'breakdown' => null,
        'tax_source' => 'fallback',
        'error_message' => $e->getMessage(),
        'location' => [
            'state' => $to_state,
            'zip' => $to_zip,
            'city' => $to_city
        ]
    ]);
}
?>