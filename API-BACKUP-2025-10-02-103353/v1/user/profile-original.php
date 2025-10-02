<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Not authenticated']));
}

$user_id = intval($_SESSION['user_id']);

// Database connection - using same method as working files
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

// Get user profile data
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
    die(json_encode(['error' => 'User not found']));
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
