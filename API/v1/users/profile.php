<?php
// User Profile API
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// For now, we'll use a default user ID or get it from session
// In production, this would come from authenticated user session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// If specific user ID is requested
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
}

// Get user profile data
$sql = "SELECT ID, Name, Email, ShipToName, ShipToDept, Phone, 
        Address1, Address2, City, State, Zip, country, 
        employee_type, Budget, BudgetBalance, CID
        FROM Users 
        WHERE ID = $user_id 
        LIMIT 1";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(array('status' => 'error', 'message' => 'Query failed')));
}

$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(array('status' => 'error', 'message' => 'User not found')));
}

// Get order history for this user
$order_history = array();
$order_sql = "SELECT ID, OrderDate, OrderTotal, Status 
              FROM Orders 
              WHERE CustomerID = $user_id 
              ORDER BY OrderDate DESC 
              LIMIT 10";

$order_result = $mysqli->query($order_sql);

if ($order_result && $order_result->num_rows > 0) {
    while ($row = $order_result->fetch_assoc()) {
        $order_history[] = array(
            'order_id' => $row['ID'],
            'date' => $row['OrderDate'],
            'total' => floatval($row['OrderTotal']),
            'status' => $row['Status']
        );
    }
}

// Format the response
$response = array(
    'status' => 'success',
    'data' => array(
        'user' => array(
            'id' => intval($user['ID']),
            'name' => $user['Name'],
            'email' => $user['Email'],
            'phone' => $user['Phone'],
            'employee_type' => $user['employee_type'],
            'department' => $user['ShipToDept']
        ),
        'budget' => array(
            'total_budget' => floatval($user['Budget']),
            'balance' => floatval($user['BudgetBalance']),
            'spent' => floatval($user['Budget']) - floatval($user['BudgetBalance'])
        ),
        'shipping_address' => array(
            'name' => $user['ShipToName'],
            'department' => $user['ShipToDept'],
            'street' => $user['Address1'],
            'street2' => $user['Address2'],
            'city' => $user['City'],
            'state' => $user['State'],
            'zip' => $user['Zip'],
            'country' => $user['country']
        ),
        'order_history' => $order_history
    )
);

echo json_encode($response);
$mysqli->close();
?>