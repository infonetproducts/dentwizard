<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "Database Tables:\n\n";
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    echo "Table: {$row[0]}\n";
}

echo "\n\nOrders Table Structure:\n";
$result = $mysqli->query("DESCRIBE Orders");
while ($row = $result->fetch_assoc()) {
    echo "{$row['Field']} | {$row['Type']} | {$row['Key']} | {$row['Extra']}\n";
}

echo "\n\nOrderItems Table Structure:\n";
$result = $mysqli->query("DESCRIBE OrderItems");
while ($row = $result->fetch_assoc()) {
    echo "{$row['Field']} | {$row['Type']} | {$row['Key']} | {$row['Extra']}\n";
}

echo "\n\nRecent Orders (Raw):\n";
$result = $mysqli->query("SELECT * FROM Orders ORDER BY OrderDate DESC LIMIT 3");
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

$mysqli->close();
?>
