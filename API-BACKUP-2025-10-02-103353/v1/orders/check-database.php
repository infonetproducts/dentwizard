<?php
// Check Orders table directly
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

// Get ALL orders from today
$today = date('Y-m-d');
$query1 = "SELECT OrderID, UserID, OrderDate, Status FROM Orders 
           WHERE OrderDate >= '$today 00:00:00' 
           ORDER BY OrderDate DESC LIMIT 10";
$result1 = $mysqli->query($query1);

$today_orders = array();
if ($result1 && $result1->num_rows > 0) {
    while ($row = $result1->fetch_assoc()) {
        $today_orders[] = array(
            'order_id' => $row['OrderID'],
            'user_id' => $row['UserID'],
            'date' => $row['OrderDate'],
            'status' => $row['Status']
        );
    }
}

// Get Jamie's specific orders
$query2 = "SELECT * FROM Orders WHERE UserID = 20296 ORDER BY ID DESC LIMIT 5";
$result2 = $mysqli->query($query2);

$jamie_orders = array();
if ($result2 && $result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        $jamie_orders[] = array(
            'id' => $row['ID'],
            'order_id' => $row['OrderID'],
            'date' => $row['OrderDate'],
            'status' => $row['Status']
        );
    }
}

// Check Jamie's current budget
$query3 = "SELECT Budget FROM Users WHERE ID = 20296";
$result3 = $mysqli->query($query3);
$jamie_budget = null;
if ($result3 && $result3->num_rows > 0) {
    $jamie_budget = $result3->fetch_assoc()['Budget'];
}

// Test if we can insert an order
$test_order_id = 'TEST-DIRECT-' . time();
$test_query = "INSERT INTO Orders (
    OrderID, OrderDate, UserID, UserLogin, Email,
    Name, Dept, DueDate, Company,
    ShipToName, ShipToDept, Phone,
    Address1, Address2, City, State, Zip,
    Notes, BillCode, BillTo, Status, ShipDate, CID
) VALUES (
    '$test_order_id', NOW(), 20296, 'jkrugger', 'jkrugger@infonetproducts.com',
    'Jamie Test', '', NOW(), '',
    'Jamie Test', '', '8144349080',
    '123 Test', '', 'Erie', 'PA', '16501',
    '', 0, '', 'test', NOW(), 244
)";

$test_result = $mysqli->query($test_query);
$test_success = $test_result ? true : false;
$test_error = $test_result ? null : $mysqli->error;

// If test succeeded, check if it exists and then delete it
$test_found = false;
if ($test_success) {
    $check_query = "SELECT ID FROM Orders WHERE OrderID = '$test_order_id'";
    $check_result = $mysqli->query($check_query);
    $test_found = $check_result && $check_result->num_rows > 0;
    
    // Clean up
    $mysqli->query("DELETE FROM Orders WHERE OrderID = '$test_order_id'");
}

die(json_encode(array(
    'today_orders_count' => count($today_orders),
    'today_orders' => $today_orders,
    'jamie_orders_count' => count($jamie_orders),
    'jamie_orders' => $jamie_orders,
    'jamie_budget' => $jamie_budget,
    'test_insert_success' => $test_success,
    'test_insert_error' => $test_error,
    'test_order_found' => $test_found,
    'current_date' => date('Y-m-d H:i:s')
), JSON_PRETTY_PRINT));
?>