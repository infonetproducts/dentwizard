<?php
// Temporary bypass profile for testing - returns Jamie's data
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// For testing - always return Jamie's data
// This bypasses authentication to prove the React app works
$response = array(
    'success' => true,
    'data' => array(
        'id' => '20296',
        'email' => 'jkrugger@infonetproducts.com',
        'name' => 'Jamie Krugger',
        'phone' => '',
        'userType' => 'standard',
        'budget' => array(
            'budget_amount' => 500.0,
            'budget_balance' => 500.0,
            'recurring' => false,
            'renewal_date' => null
        ),
        'shippingAddress' => null
    )
);

echo json_encode($response);
?>
