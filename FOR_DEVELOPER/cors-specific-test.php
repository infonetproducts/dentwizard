<?php
// TEST 1: Can we send CORS headers specifically?
header("X-Test: Working");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");

// TEST 2: Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit("OPTIONS handled");
}

// TEST 3: Return JSON response
header('Content-Type: application/json');
echo json_encode([
    "test" => "success",
    "headers_sent" => "CORS headers should be visible",
    "method" => $_SERVER['REQUEST_METHOD']
]);
?>
