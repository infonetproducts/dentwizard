<?php
// Product Detail API - Fixed version without broken table queries
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    die(json_encode(array('status' => 'error', 'message' => 'Invalid product ID')));
}

// Get the main product details
$sql = "SELECT ID, item_title, Price, ImageFile, FormID, Description, item_logo_ids, product_tax_code 
        FROM Items 
        WHERE ID = $product_id 
        AND CID = 244 
        AND status_item = 'Y' 
        LIMIT 1";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(array('status' => 'error', 'message' => 'Query failed')));
}

$product = $result->fetch_assoc();

if (!$product) {
    die(json_encode(array('status' => 'error', 'message' => 'Product not found')));
}

// Get color variants from item_group_options table
$color_variants = array();

$color_sql = "SELECT option_id, display_name, value, color_image 
              FROM item_group_options 
              WHERE item_id = $product_id 
              AND CID = 244 
              AND price = 0
              ORDER BY option_id";

$color_result = $mysqli->query($color_sql);

if ($color_result && $color_result->num_rows > 0) {
    while ($row = $color_result->fetch_assoc()) {
        $color_variants[] = array(
            'id' => intval($row['option_id']),
            'name' => $row['display_name'],
            'value' => $row['value'],
            'image' => !empty($row['color_image']) ? 'https://dentwizard.lgstore.com/pdf/244/' . $row['color_image'] : null,
            'price' => floatval($product['Price'])
        );
    }
}

// If no color variants found, use the main product as default
if (empty($color_variants)) {
    $color_variants[] = array(
        'id' => 0,
        'name' => 'Default',
        'value' => 'Default',
        'image' => 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'],
        'price' => floatval($product['Price'])
    );
}

// Get available sizes - SKIP broken ItemsSizesStyles table, use FormID extraction
$sizes = array();

// Extract sizes from FormID patterns
$base_formid = substr($product['FormID'], 0, 8);

$formid_sql = "SELECT DISTINCT FormID 
               FROM Items 
               WHERE FormID LIKE '$base_formid%' 
               AND CID = 244 
               AND status_item = 'Y'";

$formid_result = $mysqli->query($formid_sql);

if ($formid_result && $formid_result->num_rows > 0) {
    $valid_sizes = array('XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '2XL', '3XL', '4XL', '5XL',
                       'LT', 'XLT', '2LT', '3LT', 'MT', 'ST', '2XT', '3XT');
    
    while ($row = $formid_result->fetch_assoc()) {
        foreach ($valid_sizes as $size) {
            if (preg_match('/[-_]' . preg_quote($size, '/') . '$/i', $row['FormID'])) {
                if (!in_array($size, $sizes)) {
                    $sizes[] = $size;
                }
                break;
            }
        }
    }
}

// Default sizes if none found
if (empty($sizes)) {
    if (stripos($product['item_title'], 'Tall') !== false || stripos($product['item_title'], 'Big') !== false) {
        $sizes = array('LT', 'XLT', '2LT', '3LT');
    } else {
        $sizes = array('S', 'M', 'L', 'XL', '2XL', '3XL');
    }
}

// Get available logos for this product
$logos = array();
$has_logos = false;

if (!empty($product['item_logo_ids'])) {
    $item_logo_ids = $product['item_logo_ids'];
    
    // Get the logos from ClientLogos table
    $logo_sql = "SELECT ID, Name, image_name, CID 
                 FROM ClientLogos 
                 WHERE ID IN ($item_logo_ids) 
                 ORDER BY Name";
    
    $logo_result = $mysqli->query($logo_sql);
    
    if ($logo_result && $logo_result->num_rows > 0) {
        while ($row = $logo_result->fetch_assoc()) {
            $logo_image = '';
            if (!empty($row['image_name'])) {
                $logo_image = 'https://dentwizard.lgstore.com/pdf/' . $row['CID'] . '/' . $row['image_name'];
            }
            
            $logos[] = array(
                'id' => intval($row['ID']),
                'name' => $row['Name'],
                'image' => $logo_image
            );
        }
        $has_logos = true;
    }
}

// Format the complete response
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
        'description' => $product['Description'] ? $product['Description'] : 'Quality DentWizard apparel',
        'available_sizes' => $sizes,
        'color_variants' => $color_variants,
        'available_logos' => $logos,
        'has_logos' => $has_logos
    )
);

echo json_encode($response);
$mysqli->close();
?>
