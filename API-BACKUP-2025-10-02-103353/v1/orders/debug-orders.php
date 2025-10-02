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
    die(json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $mysqli->connect_error)));
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$email = isset($data['email']) ? $mysqli->real_escape_string($data['email']) : '';

if (empty($email)) {
    die(json_encode(array('success' => false, 'message' => 'User email required')));
}

// Get user ID
$userQuery = "SELECT ID FROM Users WHERE Email = '$email'";
$userResult = $mysqli->query($userQuery);

if (!$userResult) {
    die(json_encode(array('success' => false, 'message' => 'User query failed: ' . $mysqli->error)));
}

if ($userResult->num_rows === 0) {
    die(json_encode(array('success' => false, 'message' => 'User not found')));
}

$user = $userResult->fetch_assoc();
$userId = $user['ID'];

// Test simple query first
$testQuery = "SELECT COUNT(*) as cnt FROM Orders WHERE UserID = $userId";
$testResult = $mysqli->query($testQuery);

if (!$testResult) {
    die(json_encode(array('success' => false, 'message' => 'Test query failed: ' . $mysqli->error)));
}

$testRow = $testResult->fetch_assoc();

// Now try the full query
$ordersQuery = "SELECT OrderID, UserID, TotalAmount, OrderDate, Status FROM Orders WHERE UserID = $userId ORDER BY OrderDate DESC LIMIT 5";
$ordersResult = $mysqli->query($ordersQuery);

if (!$ordersResult) {
    die(json_encode(array('success' => false, 'message' => 'Orders query failed: ' . $mysqli->error, 'query' => $ordersQuery)));
}

$orders = array();
while ($order = $ordersResult->fetch_assoc()) {
    $orders[] = $order;
}

echo json_encode(array(
    'success' => true, 
    'userId' => $userId,
    'orderCount' => $testRow['cnt'],
    'orders' => $orders
));
$mysqli->close();
?>
