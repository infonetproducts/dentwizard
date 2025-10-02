<?php
// Product Detail API - Ultra Simple
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Get product ID
$product_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Database connection
$conn = mysql_connect('localhost', 'rwaf', 'Py*uhb#L#L##');
if (!$conn) {
    echo json_encode(array('status' => 'error', 'message' => 'Connection failed'));
    exit;
}

mysql_select_db('rwaf', $conn);

// Query for product
$query = "SELECT ID, item_title, Price, ImageFile, FormID, Description FROM Items WHERE ID = " . intval($product_id) . " AND CID = 244 AND status_item = 'Y'";
$result = mysql_query($query);

if (!$result) {
    echo json_encode(array('status' => 'error', 'message' => 'Query failed'));
    exit;
}

$product = mysql_fetch_assoc($result);

if (!$product) {
    echo json_encode(array('status' => 'error', 'message' => 'Product not found'));
    exit;
}

// Format response
$response = array(
    'status' => 'success',
    'data' => array(
        'id' => $product['ID'],
        'name' => $product['item_title'],
        'price' => $product['Price'],
        'image_url' => '/pdf/244/' . $product['ImageFile'],
        'sku' => $product['FormID'],
        'description' => $product['Description'] ? $product['Description'] : 'Quality DentWizard apparel'
    )
);

echo json_encode($response);
mysql_close($conn);
?>