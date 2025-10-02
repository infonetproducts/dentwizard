<?php
header("Content-Type: application/json");

$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Connection failed')));
}

$product_id = 83983;

$sql = "SELECT ID, item_title, Price, ImageFile, FormID 
        FROM Items 
        WHERE ID = $product_id 
        LIMIT 1";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(array('error' => 'Query failed')));
}

$product = $result->fetch_assoc();

if (!$product) {
    die(json_encode(array('error' => 'Product not found')));
}

echo json_encode(array(
    'id' => intval($product['ID']),
    'title' => $product['item_title'],
    'price' => floatval($product['Price']),
    'sku' => $product['FormID']
));

$mysqli->close();
?>
