<?php
// API/v1/auth/login.php
// Standard authentication for non-SSO users
// FIXED to match working API structure

session_start();

// CORS headers for credentials/cookies
header("Access-Control-Allow-Origin: http://localhost:3000"); // Must be specific origin for credentials
header("Access-Control-Allow-Credentials: true"); // Allow cookies
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection - matching working APIs structure
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$email = isset($data['email']) ? $mysqli->real_escape_string($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (empty($email) || empty($password)) {
    die(json_encode(array('error' => 'Email and password required')));
}

// Get user from database
// Using Password field (capital P) as shown in database
$sql = "SELECT 
    ID as user_id,
    Email as email,
    Name as name,
    Password as password_field,
    CID as client_id,
    Budget as budget_limit,
    BudgetBalance as budget_balance
    FROM Users 
    WHERE Email = '$email' 
    LIMIT 1";

$result = $mysqli->query($sql);

if (!$result || $result->num_rows === 0) {
    http_response_code(401);
    die(json_encode(array('error' => 'Invalid credentials')));
}

$user = $result->fetch_assoc();

// Simple password check (for testing - should use password_verify in production)
// Check if password matches
if ($user['password_field'] !== $password) {
    http_response_code(401);
    die(json_encode(array('error' => 'Invalid credentials')));
}

// Create session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['email'] = $user['email'];
$_SESSION['client_id'] = $user['client_id'];
$_SESSION['auth_method'] = 'standard';
$_SESSION['AID'] = $user['user_id']; // Match your existing session variables
$_SESSION['CID'] = $user['client_id'];

// Create a simple token (in production, use proper JWT)
$token = base64_encode(json_encode(array(
    'user_id' => $user['user_id'],
    'email' => $user['email'],
    'client_id' => $user['client_id'],
    'exp' => time() + (7 * 24 * 60 * 60) // 7 days
)));

// Return user data
echo json_encode(array(
    'success' => true,
    'token' => $token,
    'user' => array(
        'id' => intval($user['user_id']),
        'email' => $user['email'],
        'name' => $user['name'],
        'budget' => array(
            'limit' => floatval($user['budget_limit']),
            'balance' => floatval($user['budget_balance'])
        )
    ),
    'auth_method' => 'standard'
));

$mysqli->close();
?>