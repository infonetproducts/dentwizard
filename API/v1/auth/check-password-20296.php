<?php
// Check Jamie's password in database
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Check Jamie's password
$sql = "SELECT ID, Email, Password 
        FROM Users 
        WHERE ID = 20296";

$result = $mysqli->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'user_id' => $row['ID'],
        'email' => $row['Email'],
        'password_exists' => !empty($row['Password']),
        'password_length' => strlen($row['Password']),
        'password_value' => $row['Password'], // For debugging only
        'matches_password' => ($row['Password'] === 'password'),
        'password_bytes' => bin2hex($row['Password']) // Check for hidden characters
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => 'User not found']);
}

$mysqli->close();
?>