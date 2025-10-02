<?php
// Orders API - Development version without authentication
// Matches the structure of profile-dev.php which is working

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

// Fetch user's orders using UserID column (not UID!)
$sql = "SELECT 
            OrderID as id,
            CONCAT('DW-', DATE_FORMAT(OrderDate, '%Y%m%d'), '-', LPAD(OrderID, 4, '0')) as orderNumber,
            DATE_FORMAT(OrderDate, '%Y-%m-%dT%H:%i:%s') as created_at,
            Status as status,
            order_total as total,
            ShipToName as shippingName,
            CONCAT(Address1, ' ', City, ', ', State, ' ', Zip) as shippingAddress
        FROM Orders 
        WHERE UserID = $user_id 
        ORDER BY OrderDate DESC";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(array('status' => 'error', 'message' => 'Query failed: ' . $mysqli->error)));
}

$orders = array();
while ($row = $result->fetch_assoc()) {
    // Convert total to float so React can use .toFixed()
    $row['total'] = floatval($row['total']);
    $orders[] = $row;
}

// Return orders array directly (React expects plain array)
echo json_encode($orders);

$mysqli->close();
?>