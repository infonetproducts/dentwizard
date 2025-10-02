<?php
// DEBUG VERSION - User Addresses API
// This version includes error reporting to help identify the issue

// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Test basic PHP works
echo json_encode(array('test' => 'PHP is working', 'version' => phpversion()));
exit();

// Rest of code commented out for now to isolate the issue
/*
// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed: ' . $mysqli->connect_error)));
}

echo json_encode(array('status' => 'success', 'message' => 'Database connected'));
*/
?>