<?php
/**
 * Shipping Methods API Endpoint
 * Deploy to: /lg/API/v1/shipping/methods.php
 */

session_start();
include_once("../../include/db.php");

header('Content-Type: application/json');

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : $_SESSION['CID'];
$subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;

$methods = [];
$free_shipping_clients = [56, 59, 62, 63, 72, 78, 89];

// Special pickup options
if ($client_id == 61) {
    $methods[] = ['id' => 'pickup_leader', 'name' => 'FREE Pickup at Leader Graphics',
                  'description' => '1107 Hess Ave, Erie, PA 16503', 'cost' => 0];
}
if ($client_id == 62) {
    $methods[] = ['id' => 'hospital_delivery', 'name' => 'Delivered to Titusville Area Hospital',
                  'description' => '10-14 Business Days', 'cost' => 0];
}
if ($client_id == 56 || $client_id == 63) {
    $methods[] = ['id' => 'pickup_school', 'name' => 'Pickup at School',
                  'description' => 'Available when ready', 'cost' => 0];
}

// Standard shipping
if (in_array($client_id, $free_shipping_clients)) {
    $methods[] = ['id' => 'standard_free', 'name' => 'FREE Standard Shipping',
                  'description' => '7-10 business days', 'cost' => 0];
} else {
    $methods[] = ['id' => 'standard', 'name' => 'Standard Shipping',
                  'description' => '7-10 business days', 'cost' => 10];
}

echo json_encode(['success' => true, 'methods' => $methods]);
?>