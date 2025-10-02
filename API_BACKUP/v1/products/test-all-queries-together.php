<?php
header("Content-Type: application/json");
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
$product_id = 83983;

// Run ALL queries like detail.php does
$sql = "SELECT ID, item_title, Price, ImageFile, FormID, Description, item_logo_ids, product_tax_code FROM Items WHERE ID = $product_id AND CID = 244 AND status_item = 'Y' LIMIT 1";
$result = $mysqli->query($sql);
$product = $result->fetch_assoc();

$color_sql = "SELECT option_id, display_name, value, color_image FROM item_group_options WHERE item_id = $product_id AND CID = 244 AND price = 0 ORDER BY option_id";
$color_result = $mysqli->query($color_sql);

$base_formid = substr($product['FormID'], 0, 8);
$formid_sql = "SELECT DISTINCT FormID FROM Items WHERE FormID LIKE '$base_formid%' AND CID = 244 AND status_item = 'Y'";
$formid_result = $mysqli->query($formid_sql);

$item_logo_ids = $product['item_logo_ids'];
$logo_sql = "SELECT ID, Name, image_name, CID FROM ClientLogos WHERE ID IN ($item_logo_ids) ORDER BY Name";
$logo_result = $mysqli->query($logo_sql);

echo json_encode(array('test' => 'all queries executed', 'product_title' => $product['item_title'], 'colors_count' => $color_result->num_rows, 'formids_count' => $formid_result->num_rows, 'logos_count' => $logo_result->num_rows));
$mysqli->close();
?>
