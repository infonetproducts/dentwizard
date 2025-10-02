<?php
/**
 * SIMPLE TEST FILE - Upload this EXACTLY as shown to test CORS
 * Upload to: /lg/API/cors-test-simple.php
 */

// CORS Headers - MUST BE FIRST THING (no spaces or lines before <?php)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Send JSON response
header('Content-Type: application/json');

// Test response
echo json_encode([
    'status' => 'success',
    'message' => 'If you can see this, CORS is working!',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>
