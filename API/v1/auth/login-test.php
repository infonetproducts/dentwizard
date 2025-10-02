<?php
// Test login for Jamie - super simple version
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// For testing - accept both POST and GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Test mode - show current session
    echo json_encode([
        'test_mode' => true,
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'message' => 'Use POST with email and password to login'
    ]);
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? $input['email'] : '';
$password = isset($input['password']) ? $input['password'] : '';

// Debug info
error_log("Login attempt - Email: $email");

// Connect to database
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]));
}

// Get user from database
$sql = "SELECT ID, Email, Name, UserType, Budget, BudgetBalance, Password 
        FROM Users 
        WHERE Email = '" . $mysqli->real_escape_string($email) . "'";

$result = $mysqli->query($sql);

if (!$result) {
    http_response_code(500);
    die(json_encode(['error' => 'Query failed: ' . $mysqli->error]));
}

$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(401);
    die(json_encode(['error' => 'User not found with email: ' . $email]));
}

// For Jamie, accept "password" regardless of what's in database
$password_valid = false;
if ($email === 'jkrugger@infonetproducts.com' && $password === 'password') {
    $password_valid = true;
} else {
    // For other users, check actual password
    $password_valid = ($user['Password'] === $password);
}

if (!$password_valid) {
    http_response_code(401);
    die(json_encode([
        'error' => 'Invalid password',
        'debug' => [
            'password_provided' => $password,
            'password_in_db' => substr($user['Password'], 0, 3) . '...' // Show first 3 chars for debug
        ]
    ]));
}

// SUCCESS - Set session variables
$_SESSION['user_id'] = $user['ID'];
$_SESSION['userEmail'] = $user['Email'];
$_SESSION['userName'] = $user['Name'];
$_SESSION['userType'] = $user['UserType'];

// Log successful login
error_log("Login successful - User ID: " . $user['ID'] . ", Name: " . $user['Name']);

// Return success response
$response = [
    'success' => true,
    'data' => [
        'user' => [
            'id' => $user['ID'],
            'email' => $user['Email'],
            'name' => $user['Name'],
            'userType' => $user['UserType'],
            'budget' => [
                'budget_amount' => (float)$user['Budget'],
                'budget_balance' => (float)$user['BudgetBalance']
            ]
        ],
        'token' => session_id(),
        'session_test' => $_SESSION // Include session data for debugging
    ]
];

$mysqli->close();

echo json_encode($response);
?>
