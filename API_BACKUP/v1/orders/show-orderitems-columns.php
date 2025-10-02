<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

// Show all columns in OrderItems table
$result = $mysqli->query("SHOW COLUMNS FROM OrderItems");
$columns = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

echo json_encode(array('success' => true, 'columns' => $columns));
$mysqli->close();
?>
