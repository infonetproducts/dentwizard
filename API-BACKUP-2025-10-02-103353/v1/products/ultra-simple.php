<?php
header("Content-Type: application/json");
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
$result = $mysqli->query("SELECT ID, item_title FROM Items WHERE ID = 83983 LIMIT 1");
$product = $result->fetch_assoc();
echo json_encode(['id' => $product['ID'], 'title' => $product['item_title']]);
$mysqli->close();
?>
