<?php
// Test Product Detail API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Test basic functionality
$test_id = isset($_GET['id']) ? $_GET['id'] : 'no_id';

// Test response
echo json_encode(array(
    'status' => 'success',
    'message' => 'API endpoint is working',
    'received_id' => $test_id,
    'php_version' => phpversion(),
    'data' => array(
        'id' => 1,
        'name' => 'Test Product',
        'price' => 99.99,
        'image_url' => '/placeholder.png'
    )
));
?>