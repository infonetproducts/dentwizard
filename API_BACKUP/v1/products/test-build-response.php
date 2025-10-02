<?php
header("Content-Type: application/json");
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
$product_id = 83983;

$sql = "SELECT ID, item_title, Price, ImageFile, FormID, Description, item_logo_ids, product_tax_code FROM Items WHERE ID = $product_id AND CID = 244 AND status_item = 'Y' LIMIT 1";
$result = $mysqli->query($sql);
$product = $result->fetch_assoc();

// Try to build the response just like detail.php does
$response = array(
    'status' => 'success',
    'data' => array(
        'id' => intval($product['ID']),
        'name' => $product['item_title'],
        'price' => floatval($product['Price']),
        'tax_code' => $product['product_tax_code'],
        'image_url' => 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'],
        'image_file' => $product['ImageFile'],
        'sku' => $product['FormID'],
        'description' => $product['Description'] ? $product['Description'] : 'Quality DentWizard apparel'
    )
);

$json = json_encode($response);
if ($json === false) {
    echo json_encode(array('error' => 'json_encode failed', 'json_error' => json_last_error_msg()));
} else {
    echo $json;
}
$mysqli->close();
?>
