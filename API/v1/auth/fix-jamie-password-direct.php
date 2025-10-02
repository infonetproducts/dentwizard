<?php
// Direct SQL update to set Jamie's password to plain text "password"
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Directly set the password to "password" (plain text)
$sql = "UPDATE Users SET Password = 'password' WHERE ID = 20296";
$update_result = $mysqli->query($sql);

// Verify the update
$check_sql = "SELECT ID, Email, Password FROM Users WHERE ID = 20296";
$result = $mysqli->query($check_sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'update_success' => $update_result,
        'user_id' => $row['ID'],
        'email' => $row['Email'],
        'password_now' => $row['Password'],
        'matches_password' => ($row['Password'] === 'password'),
        'message' => ($row['Password'] === 'password') ? 
            'Password successfully set to "password"' : 
            'Password update may have been intercepted by a trigger or stored procedure'
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => 'User not found']);
}

$mysqli->close();
?>