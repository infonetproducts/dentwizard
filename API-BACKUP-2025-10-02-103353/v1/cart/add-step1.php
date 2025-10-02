<?php
// Add to Cart - Step by Step Debug
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Function to safely output and exit
function output_json($data) {
    echo json_encode($data);
    exit();
}

// Step 1: Test basic PHP
output_json(array(
    'step' => 1,
    'success' => true,
    'message' => 'PHP is working',
    'php_version' => phpversion()
));
?>