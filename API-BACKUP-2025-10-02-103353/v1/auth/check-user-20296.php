<?php
// Check user 20296 (Jamie Krugger) data
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection - using correct credentials
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Check user 20296's data
$sql = "SELECT ID, FirstName, LastName, Email, Password 
        FROM Users 
        WHERE ID = 20296";

$result = $mysqli->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $password_check = ($row['Password'] === 'dentwizard');
    
    echo json_encode([
        'user_found' => true,
        'id' => $row['ID'],
        'name' => $row['FirstName'] . ' ' . $row['LastName'],
        'email' => $row['Email'],
        'password_exists' => !empty($row['Password']),
        'password_matches_dentwizard' => $password_check,
        'message' => $password_check ? 
            'Password is correct! Login should work.' : 
            'Password needs to be updated to dentwizard'
    ], JSON_PRETTY_PRINT);
    
    if (!$password_check) {
        echo "\n\n// Run this SQL to fix the password:\n";
        echo "// UPDATE Users SET Password = 'dentwizard' WHERE ID = 20296;\n";
    }
} else {
    echo json_encode(['user_found' => false]);
}

$mysqli->close();
?>