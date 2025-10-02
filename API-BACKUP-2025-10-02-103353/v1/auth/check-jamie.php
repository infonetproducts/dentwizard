<?php
// Check Jamie (not Jamison) user data
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection - using correct credentials
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Check Jamie's data (note: Jamie, not Jamison)
$sql = "SELECT ID, FirstName, LastName, Email, Password 
        FROM Users 
        WHERE Email = 'jkrugger@infonetproducts.com'
        OR (FirstName = 'Jamie' AND LastName = 'Krugger')";

$result = $mysqli->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'user_found' => true,
        'id' => $row['ID'],
        'name' => $row['FirstName'] . ' ' . $row['LastName'],
        'email' => $row['Email'],
        'password_exists' => !empty($row['Password']),
        'password_length' => strlen($row['Password']),
        'password_value' => $row['Password'], // For debugging only
        'test_match' => ($row['Password'] === 'dentwizard'),
        'note' => 'This is Jamie (not Jamison) Krugger'
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'user_found' => false,
        'message' => 'No user found'
    ]);
}

// Also provide SQL to set Jamie's password
echo "\n\n// SQL to set password:\n";
echo "// UPDATE Users SET Password = 'dentwizard' WHERE Email = 'jkrugger@infonetproducts.com';";

$mysqli->close();
?>