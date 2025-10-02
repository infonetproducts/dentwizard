<?php

/**
 * CORS Test Endpoint
 * Upload this file to /lg/API/test-cors.php
 * Test by visiting: https://dentwizard.lgstore.com/lg/API/test-cors.php
 */

// Include CORS headers
require_once 'cors.php';

// Set JSON content type
header('Content-Type: application/json');

// Test response data
$response = array(
    'status' => 'success',
    'message' => 'CORS is working correctly!',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => array(
        'php_version' => phpversion(),
        'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
        'request_method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'Unknown',
        'origin' => isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'No origin header',
        'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'Unknown',
    ),
    'cors_headers_sent' => array(
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-Session-ID',
    ),
    'test_endpoints' => array(
        'products' => 'https://dentwizard.lgstore.com/lg/API/products/list.php',
        'categories' => 'https://dentwizard.lgstore.com/lg/API/categories/list.php',
        'cart' => 'https://dentwizard.lgstore.com/lg/API/cart/get.php',
    )
);

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);

?>