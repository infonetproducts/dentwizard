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

// Get database connection
$conn = getDBConnection();
$CID = getCID();

// Query to get categories
// Based on your original structure, categories are in a separate table
// This is a simplified version - adjust based on your actual category table structure
$sql = "SELECT DISTINCT 
    fcl.CategoryID as id,
    c.category_name as name,
    c.parent_category_id as parent_id,
    COUNT(DISTINCT i.ID) as product_count
FROM FormCategoryLink fcl
LEFT JOIN Items i ON fcl.FormID = i.ID AND i.status_item = 'Y' AND i.CID = $CID
LEFT JOIN categories c ON fcl.CategoryID = c.cat_id AND c.CID = $CID
WHERE c.status = 'Y' OR c.status IS NULL
GROUP BY fcl.CategoryID
ORDER BY c.category_order ASC, c.category_name ASC";

// Execute query
$result = mysqli_query($conn, $sql);

if (!$result) {
    // If categories table doesn't exist, return a default set
    // This allows the app to work even without categories
    echo json_encode([
        'success' => true,
        'data' => [
            ['id' => 1, 'name' => 'All Products', 'parent_id' => 0, 'product_count' => 0]
        ]
    ]);
    exit;
}

// Build categories array
$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = [
        'id' => intval($row['id']),
        'name' => $row['name'] ?: 'Category ' . $row['id'],
        'parent_id' => intval($row['parent_id'] ?: 0),
        'product_count' => intval($row['product_count']),
        'slug' => strtolower(str_replace(' ', '-', $row['name'] ?: 'category-' . $row['id']))
    ];
}

// If no categories found, add a default one
if (empty($categories)) {
    $categories[] = [
        'id' => 1,
        'name' => 'All Products',
        'parent_id' => 0,
        'product_count' => 0,
        'slug' => 'all-products'
    ];
}

// Close connection
mysqli_close($conn);

// Return response
echo json_encode([
    'success' => true,
    'data' => $categories
], JSON_PRETTY_PRINT);
?>
