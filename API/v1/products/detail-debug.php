<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
set_time_limit(10); // 10 second timeout

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

file_put_contents(__DIR__ . '/debug.log', "=== START: " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
file_put_contents(__DIR__ . '/debug.log', "Product ID: $product_id\n", FILE_APPEND);

if ($product_id <= 0) {
    die(json_encode(['error' => 'Invalid product ID']));
}

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    file_put_contents(__DIR__ . '/debug.log', "DB Error: " . $mysqli->connect_error . "\n", FILE_APPEND);
    die(json_encode(['error' => 'Database connection failed']));
}

file_put_contents(__DIR__ . '/debug.log', "DB connected\n", FILE_APPEND);

// Main product query
$sql = "SELECT ID, item_title, Price, ImageFile, FormID, Description, item_logo_ids, product_tax_code 
        FROM Items 
        WHERE ID = $product_id 
        AND CID = 244 
        AND status_item = 'Y' 
        LIMIT 1";

file_put_contents(__DIR__ . '/debug.log', "Running main query\n", FILE_APPEND);
$result = $mysqli->query($sql);

if (!$result) {
    file_put_contents(__DIR__ . '/debug.log', "Query failed: " . $mysqli->error . "\n", FILE_APPEND);
    die(json_encode(['error' => 'Query failed', 'details' => $mysqli->error]));
}

$product = $result->fetch_assoc();

if (!$product) {
    file_put_contents(__DIR__ . '/debug.log', "Product not found\n", FILE_APPEND);
    die(json_encode(['error' => 'Product not found']));
}

file_put_contents(__DIR__ . '/debug.log', "Product found, getting colors\n", FILE_APPEND);

// Get color variants
$color_variants = [];
$color_sql = "SELECT option_id, display_name, value, color_image 
              FROM item_group_options 
              WHERE item_id = $product_id 
              AND CID = 244 
              AND price = 0
              ORDER BY option_id";

$color_result = $mysqli->query($color_sql);
file_put_contents(__DIR__ . '/debug.log', "Color query done, rows: " . ($color_result ? $color_result->num_rows : 0) . "\n", FILE_APPEND);

if ($color_result && $color_result->num_rows > 0) {
    while ($row = $color_result->fetch_assoc()) {
        $color_variants[] = [
            'id' => intval($row['option_id']),
            'name' => $row['display_name'],
            'value' => $row['value'],
            'image' => !empty($row['color_image']) ? 'https://dentwizard.lgstore.com/pdf/244/' . $row['color_image'] : null,
            'price' => floatval($product['Price'])
        ];
    }
}

if (empty($color_variants)) {
    $color_variants[] = [
        'id' => 0,
        'name' => 'Default',
        'value' => 'Default',
        'image' => 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'],
        'price' => floatval($product['Price'])
    ];
}

file_put_contents(__DIR__ . '/debug.log', "Getting sizes\n", FILE_APPEND);

// Get sizes
$sizes = [];
$size_sql = "SELECT DISTINCT Size 
             FROM ItemsSizesStyles 
             WHERE ItemID = $product_id 
             AND Size != '' 
             AND Size IS NOT NULL
             ORDER BY Size";

$size_result = $mysqli->query($size_sql);
file_put_contents(__DIR__ . '/debug.log', "Size query done, rows: " . ($size_result ? $size_result->num_rows : 0) . "\n", FILE_APPEND);

if ($size_result && $size_result->num_rows > 0) {
    while ($row = $size_result->fetch_assoc()) {
        $sizes[] = $row['Size'];
    }
}

// If no sizes, use defaults
if (empty($sizes)) {
    file_put_contents(__DIR__ . '/debug.log', "No sizes found, using defaults\n", FILE_APPEND);
    $sizes = ['S', 'M', 'L', 'XL', '2XL', '3XL'];
}

file_put_contents(__DIR__ . '/debug.log', "Getting logos\n", FILE_APPEND);

// Get logos
$logos = [];
$has_logos = false;

if (!empty($product['item_logo_ids'])) {
    $item_logo_ids = $product['item_logo_ids'];
    file_put_contents(__DIR__ . '/debug.log', "Logo IDs: $item_logo_ids\n", FILE_APPEND);
    
    $logo_sql = "SELECT ID, Name, image_name, CID 
                 FROM ClientLogos 
                 WHERE ID IN ($item_logo_ids) 
                 ORDER BY Name";
    
    $logo_result = $mysqli->query($logo_sql);
    
    if ($logo_result === false) {
        file_put_contents(__DIR__ . '/debug.log', "Logo query FAILED: " . $mysqli->error . "\n", FILE_APPEND);
    } else {
        file_put_contents(__DIR__ . '/debug.log', "Logo query success, rows: " . $logo_result->num_rows . "\n", FILE_APPEND);
        
        if ($logo_result->num_rows > 0) {
            while ($row = $logo_result->fetch_assoc()) {
                $logo_image = '';
                if (!empty($row['image_name'])) {
                    $logo_image = 'https://dentwizard.lgstore.com/pdf/' . $row['CID'] . '/' . $row['image_name'];
                }
                
                $logos[] = [
                    'id' => intval($row['ID']),
                    'name' => $row['Name'],
                    'image' => $logo_image
                ];
            }
            $has_logos = true;
        }
    }
}

file_put_contents(__DIR__ . '/debug.log', "Building response\n", FILE_APPEND);

$response = [
    'status' => 'success',
    'data' => [
        'id' => intval($product['ID']),
        'name' => $product['item_title'],
        'price' => floatval($product['Price']),
        'tax_code' => $product['product_tax_code'],
        'image_url' => 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'],
        'image_file' => $product['ImageFile'],
        'sku' => $product['FormID'],
        'description' => $product['Description'] ?: 'Quality DentWizard apparel',
        'available_sizes' => $sizes,
        'color_variants' => $color_variants,
        'available_logos' => $logos,
        'has_logos' => $has_logos
    ]
];

file_put_contents(__DIR__ . '/debug.log', "=== SUCCESS ===\n\n", FILE_APPEND);

echo json_encode($response);
$mysqli->close();
?>
