<?php
// Check what's wrong with product 83983
header("Content-Type: application/json");

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Database connection failed')));
}

$product_id = 83983;

// Get the product
$sql = "SELECT ID, item_title, Price, ImageFile, FormID, Description, item_logo_ids, product_tax_code 
        FROM Items 
        WHERE ID = $product_id 
        AND CID = 244 
        LIMIT 1";

$result = $mysqli->query($sql);
$product = $result->fetch_assoc();

echo json_encode(array(
    'success' => true,
    'product_found' => ($product ? true : false),
    'product_data' => $product,
    'item_logo_ids' => $product ? $product['item_logo_ids'] : null,
    'logo_query_would_be' => $product && !empty($product['item_logo_ids']) ? 
        "SELECT ID, Name, image_name, CID FROM ClientLogos WHERE ID IN (" . $product['item_logo_ids'] . ")" : 
        "N/A - no logo IDs"
), JSON_PRETTY_PRINT);

$mysqli->close();
?>
