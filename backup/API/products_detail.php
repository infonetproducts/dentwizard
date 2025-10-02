<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Database connection
$mysqli = new mysqli("localhost", "rwaf", "Py*uhb$L$L##", "rwaf");

if ($mysqli->connect_error) {
    echo json_encode(array('status' => 'error', 'message' => 'Database connection failed'));
    exit;
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

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

// Get color variants
// Strategy: Find products with similar base name but different color descriptors
$color_variants = array();

// Extract base product name without size/color indicators
$base_title = $product['item_title'];
// Remove common size indicators
$base_title = preg_replace('/\s+(XS|S|M|L|XL|XXL|XXXL|2XL|3XL|LT|XLT|2LT|3LT)$/i', '', $base_title);

// Extract base FormID without size
$base_formid = preg_replace('/[-_](XS|S|M|L|XL|XXL|XXXL|2XL|3XL|LT|XLT|2LT|3LT)$/i', '', $product['FormID']);

// Look for products with same base FormID but potentially different middle parts (colors)
// For example: BCK01168-Atlas-LT vs BCK01168-Polished-LT
$color_query = "SELECT DISTINCT ID, item_title, Price, ImageFile, FormID 
                FROM Items 
                WHERE FormID LIKE ? 
                AND CID = 244 
                AND status_item = 'Y'
                ORDER BY item_title";

// Use the base FormID pattern
$color_pattern = substr($base_formid, 0, 8) . '%'; // Get first 8 chars of FormID as base
$color_stmt = $mysqli->prepare($color_query);
$color_stmt->bind_param("s", $color_pattern);
$color_stmt->execute();
$color_result = $color_stmt->get_result();

$seen_colors = array();
while ($row = $color_result->fetch_assoc()) {
    // Try to extract color from FormID or title
    $color_name = '';
    
    // Common color descriptors in your products
    $color_patterns = array(
        'Atlas' => 'Atlas',
        'Polished' => 'Polished', 
        'Black' => 'Black',
        'Navy' => 'Navy',
        'Gray' => 'Gray',
        'White' => 'White',
        'Blue' => 'Blue',
        'Red' => 'Red',
        'Charcoal' => 'Charcoal',
        'Heather' => 'Heather'
    );
    
    // Check FormID for color indicators
    foreach ($color_patterns as $pattern => $name) {
        if (stripos($row['FormID'], $pattern) !== false) {
            $color_name = $name;
            break;
        }
    }
    
    // If no color found in FormID, check title
    if (empty($color_name)) {
        foreach ($color_patterns as $pattern => $name) {
            if (stripos($row['item_title'], $pattern) !== false) {
                $color_name = $name;
                break;
            }
        }
    }
    
    // If still no color, use "Default"
    if (empty($color_name)) {
        $color_name = 'Default';
    }
    
    // Add to color variants if not already seen
    if (!isset($seen_colors[$color_name])) {
        $color_variants[] = array(
            'id' => intval($row['ID']),
            'name' => $color_name,
            'image' => $row['ImageFile'] ? 'https://dentwizard.lgstore.com/pdf/244/' . $row['ImageFile'] : null,
            'price' => floatval($row['Price'])
        );
        $seen_colors[$color_name] = true;
    }
}

// If no color variants found, add the current product as default
if (empty($color_variants)) {
    $color_variants[] = array(
        'id' => intval($product['ID']),
        'name' => 'Default',
        'image' => $product['ImageFile'] ? 'https://dentwizard.lgstore.com/pdf/244/' . $product['ImageFile'] : null,
        'price' => floatval($product['Price'])
    );
}

// Get available sizes (existing logic)
$valid_sizes = array(
    'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '2XL', '3XL', '4XL', '5XL',
    'LT', 'XLT', '2LT', '3LT', 'MT', 'ST', '2XT', '3XT',
    'SMALL', 'MEDIUM', 'LARGE', 'X-LARGE', 'XX-LARGE', 'XXX-LARGE'
);

$sizes = array();
$variant_query = "SELECT DISTINCT FormID FROM Items WHERE FormID LIKE ? AND CID = 244 AND status_item = 'Y' ORDER BY FormID";
$like_pattern = $base_formid . '%';
$variant_stmt = $mysqli->prepare($variant_query);
$variant_stmt->bind_param("s", $like_pattern);
$variant_stmt->execute();
$variant_result = $variant_stmt->get_result();

while ($row = $variant_result->fetch_assoc()) {
    foreach ($valid_sizes as $size) {
        if (preg_match('/[-_]' . preg_quote($size, '/') . '$/i', $row['FormID'])) {
            if (!in_array($size, $sizes)) {
                $sizes[] = $size;
            }
            break;
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
        'available_sizes' => $sizes,
        'color_variants' => $color_variants
    )
);

echo json_encode($response);
$mysqli->close();
?>
