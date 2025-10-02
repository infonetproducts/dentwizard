<?php
// API endpoint to check Jamison's user data
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection - using correct credentials from working APIs
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Check Jamison's data
$sql = "SELECT ID, FirstName, LastName, Email, Password 
        FROM Users 
        WHERE Email = 'jkrugger@infonetproducts.com'";

$result = $mysqli->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'user_found' => true,
        'id' => $row['ID'],
        'name' => $row['FirstName'] . ' ' . $row['LastName'],
        'email' => $row['Email'],
        'password_exists' => !empty($row['Password']),
        'password_length' => strlen($row['Password']),
        'password_value' => $row['Password'], // For debugging only - remove in production!
        'test_match' => ($row['Password'] === 'dentwizard')
    ]);
} else {
    echo json_encode([
        'user_found' => false,
        'message' => 'No user found with email: jkrugger@infonetproducts.com'
    ]);
}

$mysqli->close();
?>