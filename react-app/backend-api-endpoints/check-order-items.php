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

// Check items for specific orders
$orderIds = array('0930-095017-20296', '0928-224025-20296');
$results = array();

foreach ($orderIds as $orderId) {
    $query = "SELECT * FROM OrderItems WHERE FormID = '" . $mysqli->real_escape_string($orderId) . "'";
    $result = $mysqli->query($query);
    
    $items = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    $results[$orderId] = array(
        'count' => count($items),
        'items' => $items
    );
}

echo json_encode(array('success' => true, 'results' => $results));
$mysqli->close();
?>
