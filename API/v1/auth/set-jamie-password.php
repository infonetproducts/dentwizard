<?php
// Set Jamie's password and verify
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// First, set the password for user 20296 (Jamie)
$update_sql = "UPDATE Users SET Password = 'dentwizard' WHERE ID = 20296";
$mysqli->query($update_sql);

// Now verify it's set
$sql = "SELECT ID, FirstName, LastName, Email, Password 
        FROM Users 
        WHERE ID = 20296";

$result = $mysqli->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'user_id' => $row['ID'],
        'name' => $row['FirstName'] . ' ' . $row['LastName'],
        'email' => $row['Email'],
        'password_set' => ($row['Password'] === 'dentwizard'),
        'password_value' => $row['Password'],
        'message' => 'Password has been set to dentwizard'
    ], JSON_PRETTY_PRINT);
}

$mysqli->close();
?>