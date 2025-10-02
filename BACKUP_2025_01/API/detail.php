<?php
// Product Detail API - Fixed Size Detection
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid ID'));
    exit;
}

// Database connection
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    echo json_encode(array('status' => 'error', 'message' => 'Connection failed'));
    exit;
}

// Get the main product
$query = "SELECT ID, item_title, Price, ImageFile, FormID, Description FROM Items WHERE ID = ? AND CID = 244 AND status_item = 'Y'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo json_encode(array('status' => 'error', 'message' => 'Product not found'));
    exit;
}

// Define valid sizes to look for
$valid_sizes = array(
    // Standard sizes
    'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '2XL', '3XL', '4XL', '5XL',
    // Tall sizes
    'LT', 'XLT', '2LT', '3LT', 'MT', 'ST', '2XT', '3XT',
    // Long form
    'SMALL', 'MEDIUM', 'LARGE', 'X-LARGE', 'XX-LARGE', 'XXX-LARGE'
);

// Try to find size variants by base FormID pattern
$sizes = array();

// Check if FormID contains size info (like BCK01168-LT or BCK01168_XL)
$base_formid = preg_replace('/[-_](XS|S|M|L|XL|XXL|XXXL|2XL|3XL|LT|XLT|2LT|3LT)$/i', '', $product['FormID']);

// Query for all products with similar FormID base
$variant_query = "SELECT DISTINCT FormID, item_title FROM Items WHERE FormID LIKE ? AND CID = 244 AND status_item = 'Y' ORDER BY FormID";
$like_pattern = $base_formid . '%';
$variant_stmt = $mysqli->prepare($variant_query);
$variant_stmt->bind_param("s", $like_pattern);
$variant_stmt->execute();
$variant_result = $variant_stmt->get_result();

while ($row = $variant_result->fetch_assoc()) {
    $formid = $row['FormID'];
    
    // Extract size from FormID if present
    foreach ($valid_sizes as $size) {
        if (preg_match('/[-_]' . preg_quote($size, '/') . '$/i', $formid, $matches)) {
            $found_size = strtoupper($matches[0]);
            $found_size = trim($found_size, '-_');
            if (!in_array($found_size, $sizes)) {
                $sizes[] = $found_size;
            }
            break;
        }
    }
}

// If no sizes found through FormID, look at titles for products with the same base name
if (empty($sizes)) {
    // Clean up the title to get base product name (remove size indicators)
    $base_title = $product['item_title'];
    foreach ($valid_sizes as $size) {
        $base_title = preg_replace('/\s+' . preg_quote($size, '/') . '$/i', '', $base_title);
    }
    
    // Search for products with similar title
    $title_query = "SELECT DISTINCT item_title FROM Items WHERE item_title LIKE ? AND CID = 244 AND status_item = 'Y'";
    $title_pattern = $base_title . '%';
    $title_stmt = $mysqli->prepare($title_query);
    $title_stmt->bind_param("s", $title_pattern);
    $title_stmt->execute();
    $title_result = $title_stmt->get_result();
    
    while ($row = $title_result->fetch_assoc()) {
        $title = $row['item_title'];
        
        // Check if title ends with a valid size
        foreach ($valid_sizes as $size) {
            if (preg_match('/\s+' . preg_quote($size, '/') . '$/i', $title)) {
                if (!in_array($size, $sizes)) {
                    $sizes[] = $size;
                }
                break;
            }
        }
    }
}

// If still no sizes found, provide reasonable defaults based on product type
if (empty($sizes)) {
    // Check if it's a "Big and Tall" product
    if (stripos($product['item_title'], 'Tall') !== false || stripos($product['item_title'], 'Big') !== false) {
        $sizes = array('LT', 'XLT', '2LT', '3LT');
    } else {
        $sizes = array('S', 'M', 'L', 'XL', '2XL', '3XL');
    }
}

// Sort sizes in logical order
$size_order = array('XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6, '2XL' => 6, 'XXXL' => 7, '3XL' => 7, 
                    'ST' => 2, 'MT' => 3, 'LT' => 4, 'XLT' => 5, '2LT' => 6, '2XT' => 6, '3LT' => 7, '3XT' => 7);
usort($sizes, function($a, $b) use ($size_order) {
    $order_a = isset($size_order[$a]) ? $size_order[$a] : 99;
    $order_b = isset($size_order[$b]) ? $size_order[$b] : 99;
    return $order_a - $order_b;
});

// Format response
$response = array(
    'status' => 'success',
    'data' => array(
        'id' => intval($product['ID']),
        'name' => $product['item_title'],
        'price' => floatval($product['Price']),
        'image_url' => 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'],
        'image_file' => $product['ImageFile'],
        'sku' => $product['FormID'],
        'description' => $product['Description'] ? $product['Description'] : 'Quality DentWizard apparel',
        'available_sizes' => $sizes
    )
);

echo json_encode($response);
$mysqli->close();
?>