<?php
// Shipping Methods API - Matching your working endpoint structure
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get parameters
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;

// Special client configurations from your PHP setup
$freeShippingClients = [56, 59, 62, 63, 72, 78, 89];
$pickupOnlyClients = [61, 62, 63];

$methods = [];

// Add pickup options for specific clients
if (in_array($client_id, $pickupOnlyClients)) {
    if ($client_id == 61) {
        $methods[] = [
            'method_id' => 'pickup_leader',
            'method_name' => 'FREE Pickup',
            'description' => 'Leader Graphics 1107 Hess Ave, Erie, PA 16503',
            'cost' => 0,
            'delivery_days' => '1-2 days'
        ];
    } elseif ($client_id == 62) {
        $methods[] = [
            'method_id' => 'hospital_delivery',
            'method_name' => 'Delivered to Titusville Area Hospital',
            'description' => '10-14 Business Days',
            'cost' => 0,
            'delivery_days' => '10-14 days'
        ];
    } elseif ($client_id == 56 || $client_id == 63) {
        $methods[] = [
            'method_id' => 'pickup_school',
            'method_name' => 'Pickup at School',
            'description' => 'Available for pickup',
            'cost' => 0,
            'delivery_days' => '3-5 days'
        ];
    }
}

// Add standard shipping option
if (in_array($client_id, $freeShippingClients)) {
    $methods[] = [
        'method_id' => 'standard_free',
        'method_name' => 'FREE Standard Shipping',
        'description' => '7-10 business days',
        'cost' => 0,
        'delivery_days' => '7-10 days'
    ];
} else {
    $methods[] = [
        'method_id' => 'standard',
        'method_name' => 'Standard Shipping',
        'description' => '7-10 business days',
        'cost' => 10,
        'delivery_days' => '7-10 days'
    ];
}

// Add express shipping for orders over $100
if ($subtotal > 100) {
    $methods[] = [
        'method_id' => 'express',
        'method_name' => 'Express Shipping',
        'description' => '2-3 business days',
        'cost' => in_array($client_id, $freeShippingClients) ? 15 : 25,
        'delivery_days' => '2-3 days'
    ];
}

// Return response
echo json_encode([
    'status' => 'success',
    'methods' => $methods
]);
?>