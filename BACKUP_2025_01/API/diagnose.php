<?php
// Diagnostic test for detail API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$output = array();
$output['php_version'] = phpversion();
$output['product_id'] = isset($_GET['id']) ? $_GET['id'] : 'none';

// Test database connection
$output['mysqli_available'] = function_exists('mysqli_connect');
$output['pdo_available'] = class_exists('PDO');

// Try basic connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
if ($mysqli->connect_error) {
    $output['db_connection'] = 'failed';
    $output['error'] = $mysqli->connect_error;
} else {
    $output['db_connection'] = 'success';
    
    // Try a simple query
    $result = $mysqli->query("SELECT COUNT(*) as count FROM Items WHERE CID = 244");
    if ($result) {
        $row = $result->fetch_assoc();
        $output['item_count'] = $row['count'];
    } else {
        $output['query_error'] = $mysqli->error;
    }
    $mysqli->close();
}

echo json_encode($output);
?>