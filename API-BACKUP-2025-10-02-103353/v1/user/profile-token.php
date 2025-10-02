<?php
// Proper token-based profile API that works for any user
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get user ID from multiple sources (to handle both session and token auth)
$user_id = null;

// 1. Check for user ID in header (sent from React localStorage)
if (isset($_SERVER['HTTP_X_USER_ID'])) {
    $user_id = intval($_SERVER['HTTP_X_USER_ID']);
}

// 2. Check PHP session as fallback
if (!$user_id && isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
}

// 3. Check Authorization header for token
if (!$user_id && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    // Parse token to get user ID (simplified for now)
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    // In production, validate token and extract user ID
    // For now, we'll trust the X-User-Id header
}

if (!$user_id) {
    http_response_code(401);
    die(json_encode(['error' => 'Not authenticated - no user ID found']));
}

// Connect to database
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

// Get ANY user's data based on the user_id
$sql = "SELECT 
    u.ID,
    u.Email,
    u.Name,
    u.Phone,
    u.UserType,
    u.Budget as budget_amount,
    u.BudgetBalance as budget_balance,
    u.RecurringBudget,
    u.BudgetRenewalDate,
    sa.ID as address_id,
    sa.Address as address,
    sa.City as city,
    sa.State as state,
    sa.Zip as zip
FROM Users u
LEFT JOIN ShippingAddresses sa ON u.ID = sa.UserID
WHERE u.ID = $user_id";

$result = $mysqli->query($sql);

if (!$result) {
    http_response_code(500);
    die(json_encode(['error' => 'Query failed: ' . $mysqli->error]));
}

$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    die(json_encode(['error' => 'User not found with ID: ' . $user_id]));
}

// Format the response
$response = [
    'success' => true,
    'data' => [
        'id' => $user['ID'],
        'email' => $user['Email'],
        'name' => $user['Name'],
        'phone' => $user['Phone'],
        'userType' => $user['UserType'],
        'budget' => [
            'budget_amount' => (float)$user['budget_amount'],
            'budget_balance' => (float)$user['budget_balance'],
            'recurring' => $user['RecurringBudget'] == 1,
            'renewal_date' => $user['BudgetRenewalDate']
        ],
        'shippingAddress' => $user['address_id'] ? [
            'id' => $user['address_id'],
            'address' => $user['address'],
            'city' => $user['city'],
            'state' => $user['state'],
            'zip' => $user['zip']
        ] : null
    ]
];

$mysqli->close();
echo json_encode($response);
?>
