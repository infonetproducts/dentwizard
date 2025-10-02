<?php
// Quick test to see if profile-dev.php is working
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile API Test</title>
</head>
<body>
    <h2>Testing Profile API</h2>
    <pre>
<?php
// Test database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    echo "DATABASE ERROR: " . $mysqli->connect_error . "\n";
} else {
    echo "Database connected successfully!\n\n";
    
    // Test user query
    $sql = "SELECT ID, Name, Email, Budget, BudgetBalance FROM Users WHERE ID = 1 LIMIT 1";
    $result = $mysqli->query($sql);
    
    if ($result) {
        $user = $result->fetch_assoc();
        if ($user) {
            echo "User found:\n";
            echo "ID: " . $user['ID'] . "\n";
            echo "Name: " . $user['Name'] . "\n";
            echo "Email: " . $user['Email'] . "\n";
            echo "Budget: " . $user['Budget'] . "\n";
            echo "Balance: " . $user['BudgetBalance'] . "\n";
        } else {
            echo "No user found with ID = 1\n";
        }
    } else {
        echo "Query failed: " . $mysqli->error . "\n";
    }
    
    $mysqli->close();
}
?>
    </pre>
    
    <hr>
    <h3>Test API Endpoints:</h3>
    <p><a href="/lg/API/v1/user/profile-dev.php" target="_blank">Test profile-dev.php</a></p>
    <p><a href="/lg/API/v1/user/profile-dev.php?id=1" target="_blank">Test profile-dev.php with ID=1</a></p>
</body>
</html>