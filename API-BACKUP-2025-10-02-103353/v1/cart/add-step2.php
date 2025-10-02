<?php
// Add to Cart - Step 2: Test Session
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Try to start session
$session_started = false;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    $session_started = true;
}

echo json_encode(array(
    'step' => 2,
    'success' => true,
    'session_started' => $session_started,
    'session_id' => session_id(),
    'session_status' => session_status()
));
?>