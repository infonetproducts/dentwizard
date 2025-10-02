<?php
// Enable CORS
header("Access-Control-Allow-Origin: http://localhost:3011");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error suppression for PHP 5.6 compatibility
error_reporting(0);
ini_set('display_errors', 0);

// Database configuration
$db_host = 'localhost';
$db_name = 'rwaf';
$db_user = 'rwaf';
$db_pass = 'Py*uhb#L#L##';

try {
    // Database connection
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 244;
    
    // Get all categories with parent info - no GROUP BY to preserve hierarchy
    $sql = "SELECT 
            ID as id,
            Name as name,
            ParentID as parent_id
            FROM Category 
            WHERE CID = :client_id 
            AND Active = 'Y'
            ORDER BY ParentID, Name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['client_id' => $client_id]);
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build hierarchy
    $categoriesById = [];
    $rootCategories = [];
    
    // First pass - index all categories
    foreach ($categories as &$category) {
        $category['parent_id'] = (int)$category['parent_id'];
        $category['id'] = (int)$category['id'];
        $category['subcategories'] = [];
        $categoriesById[$category['id']] = &$category;
    }
    
    // Second pass - build hierarchy
    foreach ($categoriesById as $id => &$category) {
        if ($category['parent_id'] === 0) {
            $rootCategories[] = &$category;
        } else if (isset($categoriesById[$category['parent_id']])) {
            $categoriesById[$category['parent_id']]['subcategories'][] = &$category;
        }
    }
    
    // Return both flat list and hierarchy
    echo json_encode([
        'status' => 'success',
        'data' => $categories, // Flat list for backward compatibility
        'hierarchy' => $rootCategories, // Hierarchical structure
        'count' => count($categories)
    ], JSON_PRETTY_PRINT);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        'data' => [],
        'hierarchy' => [],
        'count' => 0
    ]);
}
?>