<?php
// User Budget API - Matching your working endpoint structure

// Include centralized CORS configuration
require_once __DIR__ . '/../../cors.php';

// Set content type
header("Content-Type: application/json");

// Database connection (matching your working endpoint)
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// For testing, we'll use a hardcoded user ID
// In production, this should come from session or auth token
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;

// Get user's budget information
// Note: You'll need to create a budget table or add budget fields to your users table
$sql = "SELECT 
    budget_limit,
    budget_used,
    (budget_limit - budget_used) as budget_balance
    FROM users 
    WHERE id = $user_id 
    LIMIT 1";

$result = $mysqli->query($sql);

if (!$result) {
    // If budget table doesn't exist, return default values
    echo json_encode([
        'status' => 'success',
        'has_budget' => true,
        'balance' => 250,
        'allocated' => 250,
        'used' => 0,
        'budget_limit' => 250,
        'budget_balance' => 250,
        'currency' => 'USD'
    ]);
    exit();
}

$budget = $result->fetch_assoc();

if (!$budget) {
    // Default budget for users without budget records
    echo json_encode([
        'status' => 'success',
        'has_budget' => false,
        'balance' => 0,
        'allocated' => 0,
        'used' => 0,
        'budget_limit' => 0,
        'budget_balance' => 0,
        'currency' => 'USD'
    ]);
} else {
    // Return actual budget information
    echo json_encode([
        'status' => 'success',
        'has_budget' => true,
        'balance' => floatval($budget['budget_balance'] ?? 250),
        'allocated' => floatval($budget['budget_limit'] ?? 250),
        'used' => floatval($budget['budget_used'] ?? 0),
        'budget_limit' => floatval($budget['budget_limit'] ?? 250),
        'budget_balance' => floatval($budget['budget_balance'] ?? 250),
        'currency' => 'USD'
    ]);
}

$mysqli->close();
?>