<?php
// CORS Headers - MUST BE FIRST
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once '../db_config.php';

// Set content type
header('Content-Type: application/json');

// Get product ID from request
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid product ID'
    ]);
    exit;
}

// Get database connection
$conn = getDBConnection();
$CID = getCID();

// Query for product details with proper column names
$sql = "SELECT 
    i.ID,
    i.item_title,
    i.ImageFile,
    i.Price,
    i.FormID,
    i.Description,
    i.status_item,
    i.CID,
    i.MinQTY,
    i.MaxQTY,
    i.InventoryQuantity,
    i.item_price_type,
    i.sale_price,
    i.is_enable_sale_item,
    fcl.CategoryID
FROM Items i
LEFT JOIN FormCategoryLink fcl ON i.ID = fcl.FormID
WHERE i.ID = $product_id 
AND i.CID = $CID 
AND i.status_item = 'Y'
GROUP BY i.ID";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Product not found'
    ]);
    exit;
}

$row = mysqli_fetch_assoc($result);

// Build image URL
$image_url = '';
if (!empty($row['ImageFile'])) {
    $image_url = "https://dentwizard.lgstore.com/pdf/{$row['CID']}/{$row['ImageFile']}";
}

// Calculate price (check if on sale)
$price = floatval($row['Price']);
$original_price = null;
if ($row['is_enable_sale_item'] == 'Y' && !empty($row['sale_price'])) {
    $original_price = $price;
    $price = floatval($row['sale_price']);
}

// Get related products from same category
$related_products = [];
if (!empty($row['CategoryID'])) {
    $related_sql = "SELECT 
        i.ID,
        i.item_title,
        i.ImageFile,
        i.Price,
        i.FormID,
        i.CID
    FROM Items i
    INNER JOIN FormCategoryLink fcl ON i.ID = fcl.FormID
    WHERE fcl.CategoryID = {$row['CategoryID']}
    AND i.ID != $product_id
    AND i.CID = $CID
    AND i.status_item = 'Y'
    LIMIT 4";
    
    $related_result = mysqli_query($conn, $related_sql);
    
    if ($related_result) {
        while ($related_row = mysqli_fetch_assoc($related_result)) {
            $related_image = '';
            if (!empty($related_row['ImageFile'])) {
                $related_image = "https://dentwizard.lgstore.com/pdf/{$related_row['CID']}/{$related_row['ImageFile']}";
            }
            
            $related_products[] = [
                'id' => intval($related_row['ID']),
                'name' => $related_row['item_title'] ?: 'Untitled Product',
                'price' => floatval($related_row['Price']),
                'image_url' => $related_image,
                'sku' => $related_row['FormID'] ?: ''
            ];
        }
    }
}

// Build response with properly mapped fields
$product = [
    'id' => intval($row['ID']),
    'name' => $row['item_title'] ?: 'Untitled Product',
    'price' => $price,
    'original_price' => $original_price,
    'image_url' => $image_url,
    'images' => [$image_url], // Array of images (you can add more if you have multiple images)
    'category_id' => intval($row['CategoryID'] ?: 0),
    'sku' => $row['FormID'] ?: '',
    'description' => $row['Description'] ?: '',
    'is_active' => ($row['status_item'] == 'Y'),
    'in_stock' => intval($row['InventoryQuantity']) > 0,
    'stock_quantity' => intval($row['InventoryQuantity']),
    'min_quantity' => intval($row['MinQTY'] ?: 1),
    'max_quantity' => intval($row['MaxQTY'] ?: 999),
    'price_type' => $row['item_price_type'] ?: 'single',
    'on_sale' => ($row['is_enable_sale_item'] == 'Y'),
    'colors' => [], // Add if you have color data
    'sizes' => [],  // Add if you have size data
    'related_products' => $related_products
];

// Close connection
mysqli_close($conn);

// Return response
echo json_encode([
    'success' => true,
    'data' => $product
], JSON_PRETTY_PRINT);
?>
