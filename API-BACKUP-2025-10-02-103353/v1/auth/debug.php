<?php
// Debug test for authentication system
header("Content-Type: text/plain");

// Test database connection
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

echo "Database connected successfully\n\n";

// Check if Users table has Password field
$result = $mysqli->query("SHOW COLUMNS FROM Users");
echo "Users table columns:\n";
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    if (stripos($row['Field'], 'pass') !== false) {
        echo "  ^ Found password-related field!\n";
    }
}

echo "\n";

// Check if Jamison exists
$email = 'jkrugger@infonetproducts.com';
$sql = "SELECT ID, Name, Email FROM Users WHERE Email = '$email'";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "User found:\n";
    echo "- ID: " . $user['ID'] . "\n";
    echo "- Name: " . $user['Name'] . "\n";
    echo "- Email: " . $user['Email'] . "\n";
} else {
    echo "User not found with email: $email\n";
}

$mysqli->close();
?>