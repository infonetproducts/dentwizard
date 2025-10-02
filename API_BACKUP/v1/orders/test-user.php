<?php
// Test if Jamie's user exists
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Database connection failed')));
}

// Test different ways to find the user
$user_id = 20296;

// Method 1: Simple query
$query1 = "SELECT * FROM users WHERE ID = $user_id";
$result1 = $mysqli->query($query1);
$method1 = $result1 && $result1->num_rows > 0 ? $result1->fetch_assoc() : null;

// Method 2: Query with LIMIT
$query2 = "SELECT * FROM users WHERE ID = $user_id LIMIT 1";
$result2 = $mysqli->query($query2);
$method2 = $result2 && $result2->num_rows > 0 ? $result2->fetch_assoc() : null;

// Method 3: Check if table exists
$query3 = "SHOW TABLES LIKE 'users'";
$result3 = $mysqli->query($query3);
$table_exists = $result3 && $result3->num_rows > 0;

// Method 4: Count users
$query4 = "SELECT COUNT(*) as count FROM users";
$result4 = $mysqli->query($query4);
$user_count = $result4 ? $result4->fetch_assoc()['count'] : 0;

// Method 5: Find by email
$query5 = "SELECT * FROM users WHERE Email = 'jkrugger@infonetproducts.com'";
$result5 = $mysqli->query($query5);
$by_email = $result5 && $result5->num_rows > 0 ? $result5->fetch_assoc() : null;

die(json_encode(array(
    'table_exists' => $table_exists,
    'user_count' => $user_count,
    'method1_found' => !is_null($method1),
    'method1_user' => $method1 ? array('ID' => $method1['ID'], 'Name' => $method1['Name']) : null,
    'method2_found' => !is_null($method2),
    'by_email_found' => !is_null($by_email),
    'by_email_user' => $by_email ? array('ID' => $by_email['ID'], 'Name' => $by_email['Name']) : null
)));
?>