<?php
// Login API with proper password encoding
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Function to encode password the same way your system does
function encodePassword($password) {
    // Try different hash methods to match your system
    
    // Method 1: MD5 hash, first 8 bytes, base64
    $md5_full = md5($password, true); // true = raw binary output
    $md5_8bytes = substr($md5_full, 0, 8);
    $md5_encoded = base64_encode($md5_8bytes);
    
    // Method 2: SHA1 hash, first 8 bytes, base64
    $sha1_full = sha1($password, true);
    $sha1_8bytes = substr($sha1_full, 0, 8);
    $sha1_encoded = base64_encode($sha1_8bytes);
    
    // Method 3: SHA256 hash, first 8 bytes, base64
    $sha256_full = hash('sha256', $password, true);
    $sha256_8bytes = substr($sha256_full, 0, 8);
    $sha256_encoded = base64_encode($sha256_8bytes);
    
    return [
        'md5' => $md5_encoded,
        'sha1' => $sha1_encoded,
        'sha256' => $sha256_encoded
    ];
}

// Database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$email = $mysqli->real_escape_string($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    die(json_encode(array('error' => 'Email and password are required')));
}

// Get user from database
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
$stored_password = $user['password_field'];

// Try to match the password using different encoding methods
$encoded_passwords = encodePassword($password);
$password_valid = false;

// Check if any of our encoding methods match
foreach ($encoded_passwords as $method => $encoded) {
    if ($encoded === $stored_password) {
        $password_valid = true;
        break;
    }
}

// Also try plain text comparison (for older accounts)
if (!$password_valid && $stored_password === $password) {
    $password_valid = true;
}

// Debug: For Jamie's specific case, let's see what encoding matches
if ($email === 'jkrugger@infonetproducts.com' && !$password_valid) {
    // Return debug info instead of error
    http_response_code(401);
    die(json_encode(array(
        'error' => 'Invalid credentials',
        'debug' => [
            'stored' => $stored_password,
            'encodings_tried' => $encoded_passwords,
            'note' => 'None of the encoding methods matched'
        ]
    )));
}

if (!$password_valid) {
    http_response_code(401);
    die(json_encode(array('error' => 'Invalid credentials')));
}

// Create session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['email'] = $user['email'];
$_SESSION['client_id'] = $user['client_id'];
$_SESSION['auth_method'] = 'standard';
$_SESSION['AID'] = $user['user_id'];
$_SESSION['CID'] = $user['client_id'];

// Create a simple token
$token = base64_encode(json_encode(array(
    'user_id' => $user['user_id'],
    'email' => $user['email'],
    'client_id' => $user['client_id'],
    'exp' => time() + (7 * 24 * 60 * 60)
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
    )
));

$mysqli->close();
?>