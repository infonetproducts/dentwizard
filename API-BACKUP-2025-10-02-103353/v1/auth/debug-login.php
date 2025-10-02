<?php
// Debug version to test login
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Test database connection first
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]));
}

// Get Jamie's password from database
$email = 'jkrugger@infonetproducts.com';
$sql = "SELECT ID, Email, Password, Name FROM Users WHERE Email = '$email'";
$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . $mysqli->error]));
}

if ($result->num_rows === 0) {
    die(json_encode(['error' => 'User not found']));
}

$user = $result->fetch_assoc();

// Show what's in the database vs what we're checking
echo json_encode([
    'test_info' => [
        'user_found' => true,
        'user_id' => $user['ID'],
        'email' => $user['Email'],
        'name' => $user['Name'],
        'password_in_db' => $user['Password'],
        'password_length' => strlen($user['Password']),
        'expecting' => 'j5RLfTCRksoe=',
        'match' => ($user['Password'] === 'j5RLfTCRksoe=') ? 'YES' : 'NO'
    ]
], JSON_PRETTY_PRINT);

$mysqli->close();
?>