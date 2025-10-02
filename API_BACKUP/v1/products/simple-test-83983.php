<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$product_id = 83983;

// Just get the basic product info
$sql = "SELECT ID, item_title, item_logo_ids 
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

// Now test the logo query
$logos = [];
if (!empty($product['item_logo_ids'])) {
    $logo_sql = "SELECT ID, Name 
                 FROM ClientLogos 
                 WHERE ID IN (" . $product['item_logo_ids'] . ")";
    
    $logo_result = $mysqli->query($logo_sql);
    
    if (!$logo_result) {
        echo json_encode(['error' => 'Logo query failed', 'sql_error' => $mysqli->error]);
    } else {
        while ($row = $logo_result->fetch_assoc()) {
            $logos[] = $row;
        }
        echo json_encode(['success' => true, 'product' => $product['item_title'], 'logos' => $logos]);
    }
} else {
    echo json_encode(['success' => true, 'product' => $product['item_title'], 'message' => 'No logos configured']);
}

$mysqli->close();
?>
