<?php
// Force error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed', 'details' => $mysqli->connect_error]));
}

$product_id = 83983;

// Query WITHOUT CID and status restrictions to see actual values
$sql = "SELECT ID, item_title, CID, status_item, item_logo_ids 
        FROM Items 
        WHERE ID = $product_id 
        LIMIT 1";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(['error' => 'Query failed', 'details' => $mysqli->error]));
}

$product = $result->fetch_assoc();

if (!$product) {
    die(json_encode(['error' => 'Product does not exist at all in Items table', 'product_id' => $product_id]));
}

echo json_encode([
    'found' => true,
    'product_id' => $product['ID'],
    'title' => $product['item_title'],
    'CID' => $product['CID'],
    'status_item' => $product['status_item'],
    'item_logo_ids' => $product['item_logo_ids'],
    'issue' => 'Check if CID != 244 or status_item != Y'
], JSON_PRETTY_PRINT);

$mysqli->close();
?>
