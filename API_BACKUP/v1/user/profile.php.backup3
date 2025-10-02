<?php
// User Profile API - Fixed with correct table name
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

// Get user ID from headers
function getUserIdFromRequest() {
    // Try headers first
    foreach ($_SERVER as $key => $value) {
        if ($key == 'HTTP_X_USER_ID') {
            return $value;
        }
    }
    
    // Try GET parameter as fallback
    if (isset($_GET['user_id'])) {
        return $_GET['user_id'];
    }
    
    return null;
}

// Get token from headers  
function getTokenFromRequest() {
    foreach ($_SERVER as $key => $value) {
        if ($key == 'HTTP_X_AUTH_TOKEN') {
            return $value;
        }
    }
    return null;
}

$user_id = getUserIdFromRequest();
$token = getTokenFromRequest();

if (!$user_id || !$token) {
    die(json_encode(array('success' => false, 'message' => 'Unauthorized')));
}

// Verify token
$decoded = base64_decode($token);
if ($decoded) {
    $parts = explode(':', $decoded);
    if (count($parts) !== 3 || $parts[0] != $user_id) {
        die(json_encode(array('success' => false, 'message' => 'Invalid token')));
    }
}

// Get user profile - using correct table name: Users (capital U)
$query = "SELECT * FROM Users WHERE ID = " . intval($user_id);
$result = $mysqli->query($query);

if (!$result || $result->num_rows === 0) {
    die(json_encode(array('success' => false, 'message' => 'User not found')));
}

$user = $result->fetch_assoc();

// Format the response
$profile = array(
    'id' => $user['ID'],
    'name' => $user['Name'],
    'email' => $user['Email'],
    'phone' => isset($user['Phone']) ? $user['Phone'] : '',
    'department' => isset($user['Department']) ? $user['Department'] : '',
    'company' => isset($user['Company']) ? $user['Company'] : '',
    'userType' => isset($user['UserType']) ? $user['UserType'] : '',
    'employeeType' => isset($user['EmployeeType']) ? $user['EmployeeType'] : '',
    'clientId' => isset($user['CID']) ? $user['CID'] : 0
);

// Get budget information
$budget_amount = isset($user['Budget']) ? floatval($user['Budget']) : 0;
$budget_balance = isset($user['BudgetBalance']) ? floatval($user['BudgetBalance']) : $budget_amount;

$budget = array(
    'budget_amount' => $budget_amount,
    'budget_balance' => $budget_balance,
    'recurring' => false,
    'renewal_date' => null
);

// Return the profile with budget
die(json_encode(array(
    'success' => true,
    'profile' => $profile,
    'budget' => $budget
)));
?>