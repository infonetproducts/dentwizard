<?php
// SIMPLE Addresses API - PHP 5.3 Compatible
// Minimal version to avoid errors

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection using same format as your working files
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

// Simple connection without @ suppression to see errors
$mysqli = new mysqli($host, $user, $pass, $db);

// Check connection
if ($mysqli->connect_errno) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Database connection failed'
    ));
    exit();
}

// Get user ID
$user_id = 19346; // Default to Joe Lorenzo
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // For now, just return empty array
    // This avoids table not existing errors
    echo json_encode(array(
        'status' => 'success',
        'data' => array()
    ));
    exit();
}

if ($method == 'POST') {
    // Get input
    $input_raw = file_get_contents('php://input');
    $input = json_decode($input_raw, true);
    
    // For now, just return success
    echo json_encode(array(
        'status' => 'success',
        'message' => 'Address saving will be added after table creation'
    ));
    exit();
}

// Close connection
$mysqli->close();
?>