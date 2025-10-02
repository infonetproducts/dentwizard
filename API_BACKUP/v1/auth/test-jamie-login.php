<?php
// Test login endpoint to debug Jamie's login
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get input (hardcoded for testing)
$email = 'jkrugger@infonetproducts.com';
$password = 'password';

// Get user from database
$sql = "SELECT 
    ID as user_id,
    Email as email,
    Name as name,
    Password as password_field,
    CID as client_id
    FROM Users 
    WHERE Email = '$email' 
    LIMIT 1";

$result = $mysqli->query($sql);

if (!$result || $result->num_rows === 0) {
    die(json_encode([
        'error' => 'User not found',
        'email_searched' => $email
    ]));
}

$user = $result->fetch_assoc();

// Debug information
echo json_encode([
    'debug' => [
        'user_found' => true,
        'user_id' => $user['user_id'],
        'email' => $user['email'],
        'password_in_db' => $user['password_field'],
        'password_provided' => $password,
        'passwords_match' => ($user['password_field'] === $password),
        'password_db_length' => strlen($user['password_field']),
        'password_provided_length' => strlen($password),
        'password_db_bytes' => bin2hex($user['password_field']),
        'password_provided_bytes' => bin2hex($password)
    ],
    'would_login' => ($user['password_field'] === $password) ? 'YES' : 'NO'
], JSON_PRETTY_PRINT);

$mysqli->close();
?>