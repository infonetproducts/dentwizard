<?php
header("Content-Type: application/json");

$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$product_id = 83983;

// Main product query
$sql = "SELECT ID, item_title, Price, ImageFile, FormID, Description, item_logo_ids, product_tax_code 
        FROM Items 
        WHERE ID = $product_id 
        AND CID = 244 
        AND status_item = 'Y' 
        LIMIT 1";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(['error' => 'Query failed', 'sql_error' => $mysqli->error]));
}

$product = $result->fetch_assoc();

if (!$product) {
    die(json_encode(['error' => 'Product not found']));
}

// Just output basic product data - no colors, sizes, or logos yet
echo json_encode([
    'status' => 'success',
    'data' => [
        'id' => intval($product['ID']),
        'name' => $product['item_title'],
        'price' => floatval($product['Price']),
        'sku' => $product['FormID']
    ]
]);

$mysqli->close();
?>
