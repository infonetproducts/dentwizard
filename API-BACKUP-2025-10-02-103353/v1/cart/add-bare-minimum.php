<?php
// EXACT COPY OF get.php structure but for add
// This MUST work since get.php works

// Start session
session_start();

// Set headers - EXACT SAME AS get.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// Handle OPTIONS - EXACT SAME AS get.php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Just return success - no processing at all
echo json_encode(array(
    'success' => true,
    'message' => 'Test - no processing',
    'session_id' => session_id()
));
?>