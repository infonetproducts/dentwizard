<?php
// Check for users and help add Jamison
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection - using correct credentials
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Check for similar emails to see if there's a typo
$sql = "SELECT ID, FirstName, LastName, Email 
        FROM Users 
        WHERE Email LIKE '%krugger%' 
        OR Email LIKE '%jamison%'
        OR FirstName LIKE '%Jamison%'
        OR LastName LIKE '%Krugger%'
        LIMIT 10";

$result = $mysqli->query($sql);
$similar_users = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $similar_users[] = $row;
    }
}

// Get a sample of recent users to see the table structure
$sql2 = "SELECT ID, FirstName, LastName, Email 
         FROM Users 
         ORDER BY ID DESC 
         LIMIT 5";

$result2 = $mysqli->query($sql2);
$recent_users = [];

if ($result2) {
    while ($row = $result2->fetch_assoc()) {
        $recent_users[] = $row;
    }
}

// Provide SQL to add Jamison
$add_jamison_sql = "INSERT INTO Users (FirstName, LastName, Email, Password) 
VALUES ('Jamison', 'Krugger', 'jkrugger@infonetproducts.com', 'dentwizard');";

echo json_encode([
    'jamison_search' => $similar_users,
    'recent_users' => $recent_users,
    'sql_to_add_jamison' => $add_jamison_sql,
    'message' => count($similar_users) > 0 ? 
        'Found similar users - maybe a typo?' : 
        'No user named Jamison Krugger found - use the SQL below to add them'
], JSON_PRETTY_PRINT);

$mysqli->close();
?>