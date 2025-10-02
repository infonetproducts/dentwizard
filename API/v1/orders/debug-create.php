<?php
// Test the create.php directly with debug output
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

// Get headers
$headers = getallheaders();
$debug = array();
$debug['headers_received'] = array();
foreach ($headers as $key => $value) {
    if (in_array($key, array('X-Auth-Token', 'X-User-Id'))) {
        $debug['headers_received'][$key] = $value;
    }
}

// Test token decode
if (isset($headers['X-Auth-Token'])) {
    $token = $headers['X-Auth-Token'];
    $decoded = base64_decode($token);
    $debug['token_decoded'] = $decoded;
    
    if ($decoded) {
        $parts = explode(':', $decoded);
        $debug['token_parts'] = $parts;
    }
}

// Test database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);
$debug['db_connected'] = !$mysqli->connect_error;

// Test user lookup
if (isset($headers['X-User-Id']) && !$mysqli->connect_error) {
    $user_id = $headers['X-User-Id'];
    $query = "SELECT ID, Name, Email FROM users WHERE ID = " . intval($user_id);
    $result = $mysqli->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $debug['user_found'] = $user;
    } else {
        $debug['user_found'] = 'NOT FOUND';
    }
}

// Test minimal order insert
$test_order_id = 'DEBUG-' . time();
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
    '123 Test St', '', 'Erie', 'PA', '16501',
    '', 0, '', 'new', NOW(), 244
)";

$test_result = $mysqli->query($test_query);
$debug['test_insert'] = $test_result ? 'SUCCESS' : 'FAILED';
if (!$test_result) {
    $debug['test_error'] = $mysqli->error;
} else {
    // Clean up test
    $mysqli->query("DELETE FROM Orders WHERE OrderID = '$test_order_id'");
    $debug['test_cleanup'] = 'DELETED';
}

die(json_encode($debug, JSON_PRETTY_PRINT));
?>