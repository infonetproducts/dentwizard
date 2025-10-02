<?php
// Product Detail API - Simplified with hardcoded sizes for testing
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid ID'));
    exit;
}

// Database connection using mysqli
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    echo json_encode(array('status' => 'error', 'message' => 'Connection failed'));
    exit;
}

// Query for product
$query = "SELECT ID, item_title, Price, ImageFile, FormID, Description, Size FROM Items WHERE ID = ? AND CID = 244 AND status_item = 'Y'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(array('status' => 'error', 'message' => 'Query failed'));
    exit;
}

$product = $result->fetch_assoc();

if (!$product) {
    echo json_encode(array('status' => 'error', 'message' => 'Product not found'));
    exit;
}

// Format response with sizes
$response = array(
    'status' => 'success',
    'data' => array(
        'id' => intval($product['ID']),
        'name' => $product['item_title'],
        'price' => floatval($product['Price']),
        'image_url' => 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'],
        'image_file' => $product['ImageFile'],
        'sku' => $product['FormID'],
        'size' => $product['Size'],
        'description' => $product['Description'] ? $product['Description'] : 'Quality DentWizard apparel',
        // For now, hardcode the available sizes to ensure they show
        'available_sizes' => array('LT', 'XLT', '2LT', '3LT')
    )
);

echo json_encode($response);
$mysqli->close();
?>