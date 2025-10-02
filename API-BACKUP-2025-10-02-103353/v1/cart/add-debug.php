<?php
// Cart Add API - DEBUG VERSION
// This will help us see what error is happening

// CORS Headers - MUST be at the very top
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// TEMPORARILY ENABLE ERROR REPORTING FOR DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Test basic response first
echo json_encode(array(
    'debug' => true,
    'message' => 'Debug version running',
    'php_version' => phpversion(),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'session_status' => session_status()
));
exit();
?>