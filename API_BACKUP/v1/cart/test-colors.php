<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$mysqli = new mysqli('localhost', 'ujack102_ujack102', 'Cc$315921', 'ujack102_lgstore');

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Database connection failed')));
}

$product_id = 91754;

// Get product info
$query = "SELECT ID, item_title, ImageFile, CID FROM wp_products WHERE ID = $product_id";
$result = $mysqli->query($query);
$product = $result->fetch_assoc();

// Get color variants EXACTLY as detail.php does
$color_sql = "SELECT option_id, display_name, value, color_image 
              FROM item_group_options 
              WHERE item_id = $product_id 
              AND CID = 244 
              AND price = 0
              ORDER BY option_id";

$color_result = $mysqli->query($color_sql);

$colors = array();
if ($color_result && $color_result->num_rows > 0) {
    while ($row = $color_result->fetch_assoc()) {
        $colors[] = $row;
    }
}

$response = array(
    'product' => $product,
    'color_variants' => $colors,
    'test_urls' => array(
        'default' => 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'],
        'atlas' => '',
        'polished' => ''
    )
);

// Build URLs for each color
foreach ($colors as $color) {
    if ($color['display_name'] === 'Atlas' && !empty($color['color_image'])) {
        $response['test_urls']['atlas'] = 'https://dentwizard.lgstore.com/pdf/244/' . $color['color_image'];
    }
    if ($color['display_name'] === 'Polished' && !empty($color['color_image'])) {
        $response['test_urls']['polished'] = 'https://dentwizard.lgstore.com/pdf/244/' . $color['color_image'];
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
$mysqli->close();
?>