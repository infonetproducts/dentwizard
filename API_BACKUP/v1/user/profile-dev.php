<?php
// User Profile API - Development version without authentication
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection - matching working APIs structure
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// For development, use a default user ID or session
// In production, this would come from authentication
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 19346; // Default to Joe Lorenzo for testing

// Get user profile data matching the Users table structure
$sql = "SELECT 
    ID as user_id,
    Email as email,
    Name as name,
    Address1 as address1,
    Address2 as address2,
    City as city,
    State as state,
    Zip as zip,
    Phone as phone,
    Budget as budget_limit,
    BudgetBalance as budget_balance,
    employee_type,
    Dept as department,
    ShipToName as ship_to_name,
    ShipToDept as ship_to_dept,
    CID as client_id
    FROM Users 
    WHERE ID = $user_id 
    LIMIT 1";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(array(
        'status' => 'error', 
        'message' => 'Query failed',
        'error' => $mysqli->error
    )));
}

$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(array(
        'status' => 'error', 
        'message' => 'User not found',
        'user_id' => $user_id
    )));
}

// Calculate budget information
$budget_info = null;
if ($user['budget_limit'] !== null) {
    $budget_limit = (float)$user['budget_limit'];
    $budget_balance = (float)$user['budget_balance'];
    $budget_used = $budget_limit - $budget_balance;
    $budget_percentage = $budget_limit > 0 ? round(($budget_used / $budget_limit) * 100, 2) : 0;
    
    $budget_info = array(
        'has_budget' => true,
        'budget_limit' => $budget_limit,
        'budget_balance' => $budget_balance,
        'budget_used' => $budget_used,
        'budget_percentage' => $budget_percentage,
        'can_order' => $budget_balance > 0
    );
} else {
    $budget_info = array(
        'has_budget' => false,
        'budget_limit' => 0,
        'budget_balance' => 0,
        'budget_used' => 0,
        'budget_percentage' => 0,
        'can_order' => true
    );
}

// Get recent orders for this user
$recent_orders = array();
$order_sql = "SELECT ID, OrderDate, OrderTotal, Status 
              FROM Orders 
              WHERE CustomerID = $user_id 
              ORDER BY OrderDate DESC 
              LIMIT 5";

$order_result = $mysqli->query($order_sql);

if ($order_result && $order_result->num_rows > 0) {
    while ($row = $order_result->fetch_assoc()) {
        $recent_orders[] = array(
            'order_id' => $row['ID'],
            'date' => $row['OrderDate'],
            'total' => floatval($row['OrderTotal']),
            'status' => $row['Status']
        );
    }
}

// Format response to match existing structure
$response = array(
    'success' => true,
    'data' => array(
        // Basic Information
        'user_id' => (int)$user['user_id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'phone' => $user['phone'],
        
        // Budget Information
        'budget' => $budget_info,
        
        // Primary Address
        'primary_address' => array(
            'address1' => $user['address1'],
            'address2' => $user['address2'],
            'city' => $user['city'],
            'state' => $user['state'],
            'zip' => $user['zip']
        ),
        
        // Shipping Address
        'shipping_address' => array(
            'ship_to_name' => $user['ship_to_name'],
            'ship_to_dept' => $user['ship_to_dept'],
            'address1' => $user['address1'],
            'address2' => $user['address2'],
            'city' => $user['city'],
            'state' => $user['state'],
            'zip' => $user['zip']
        ),
        
        // Company/Organization
        'department' => $user['department'],
        'client_id' => (int)$user['client_id'],
        
        // User Type
        'employee_type' => $user['employee_type'],
        
        // Recent orders
        'recent_orders' => $recent_orders
    )
);

echo json_encode($response);
$mysqli->close();
?>