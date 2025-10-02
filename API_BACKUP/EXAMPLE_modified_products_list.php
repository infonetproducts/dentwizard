<?php
/**
 * EXAMPLE: How to modify existing PHP files
 * This shows how to add CORS to your existing products/list.php file
 */

// ============= ADD THIS SECTION AT THE VERY TOP =============
// CORS Headers - MUST BE FIRST THING IN FILE
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// ============= END OF CORS SECTION =============

// YOUR EXISTING CODE STARTS HERE...
// Example of what might be in your products/list.php file:

// Database connection
require_once('../config/database.php');

// Get query parameters
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 12;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch products from database
$sql = "SELECT * FROM products LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'products' => $products,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => 100 // You'd calculate this from a COUNT query
    ]
]);
?>
