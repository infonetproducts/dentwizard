<?php
// Simple test version to diagnose issues
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Basic test response first
$response = array(
    'status' => 'test',
    'message' => 'API is reachable',
    'php_version' => phpversion()
);

// Try database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    $response['db_status'] = 'Failed to connect';
    echo json_encode($response);
    exit();
}

$response['db_status'] = 'Connected';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response['product_id'] = $product_id;

if ($product_id <= 0) {
    $response['message'] = 'No product ID provided';
    echo json_encode($response);
    exit();
}

// Try to get product
$query = "SELECT ID, item_title, item_logo_ids FROM Items WHERE ID = $product_id LIMIT 1";
$result = $mysqli->query($query);

if (!$result) {
    $response['query_status'] = 'Query failed';
    $response['error'] = $mysqli->error;
} else {
    $product = $result->fetch_assoc();
    if ($product) {
        $response['product_found'] = true;
        $response['product_title'] = $product['item_title'];
        $response['logo_ids'] = $product['item_logo_ids'];
    } else {
        $response['product_found'] = false;
    }
}

$mysqli->close();
echo json_encode($response);
?>