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

// Get parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 12;
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? mysqli_real_escape_string(getDBConnection(), $_GET['search']) : '';

// Calculate offset
$offset = ($page - 1) * $limit;

// Get database connection
$conn = getDBConnection();
$CID = getCID();

// Build SQL query with proper column names from your database
$sql = "SELECT 
    i.ID,
    i.item_title,
    i.ImageFile, 
    i.Price,
    i.FormID,
    i.Description,
    i.status_item,
    i.CID,
    fcl.CategoryID
FROM Items i
LEFT JOIN FormCategoryLink fcl ON i.ID = fcl.FormID
WHERE i.CID = $CID 
AND i.status_item = 'Y'";

// Add category filter if provided
if ($category_id) {
    $sql .= " AND fcl.CategoryID = $category_id";
}

// Add search filter if provided
if (!empty($search)) {
    $sql .= " AND (i.item_title LIKE '%$search%' OR i.Description LIKE '%$search%')";
}

// Add ordering and limit
$sql .= " GROUP BY i.ID ORDER BY i.ID DESC LIMIT $limit OFFSET $offset";

// Execute query
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'error' => 'Query failed: ' . mysqli_error($conn)
    ]);
    exit;
}

// Build response with PROPER COLUMN MAPPING
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Build proper image URL
    $image_url = '';
    if (!empty($row['ImageFile'])) {
        // Images are stored at pdf/{CID}/{ImageFile} based on your original code
        $image_url = "https://dentwizard.lgstore.com/pdf/{$row['CID']}/{$row['ImageFile']}";
    }
    
    // Map database columns to expected JSON fields
    $product = [
        'id' => intval($row['ID']),
        'name' => $row['item_title'] ?: 'Untitled Product',  // Map item_title to name
        'price' => floatval($row['Price']),                  // Map Price to price
        'image_url' => $image_url,                           // Build URL from ImageFile
        'category_id' => intval($row['CategoryID'] ?: 0),
        'sku' => $row['FormID'] ?: '',                       // Map FormID to sku
        'description' => $row['Description'] ?: '',
        'is_active' => ($row['status_item'] == 'Y'),
        'colors' => [],  // You can populate these if you have the data
        'sizes' => [],
        'quantity_pricing' => [],
        'show_image' => true
    ];
    
    $products[] = $product;
}

// Get total count for pagination
$count_sql = "SELECT COUNT(DISTINCT i.ID) as total 
              FROM Items i
              LEFT JOIN FormCategoryLink fcl ON i.ID = fcl.FormID
              WHERE i.CID = $CID AND i.status_item = 'Y'";

if ($category_id) {
    $count_sql .= " AND fcl.CategoryID = $category_id";
}

if (!empty($search)) {
    $count_sql .= " AND (i.item_title LIKE '%$search%' OR i.Description LIKE '%$search%')";
}

$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total = intval($count_row['total']);

// Close connection
mysqli_close($conn);

// Return response
echo json_encode([
    'success' => true,
    'data' => $products,
    'pagination' => [
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ]
], JSON_PRETTY_PRINT);
?>
