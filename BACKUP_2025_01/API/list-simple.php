<?php
// CRITICAL: Suppress all PHP warnings/notices that break JSON
error_reporting(0);
ini_set('display_errors', 0);

// CORS Headers - MUST be first
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database - use @ to suppress include warnings
@include_once '../../config/database.php';

// If database.php fails, return error
if (!function_exists('getPDOConnection')) {
    echo json_encode(['error' => 'Database configuration missing']);
    exit;
}

try {
    $pdo = getPDOConnection();
    $base_url = getBaseUrl();
    
    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $client_id = isset($_GET['client_id']) ? $_GET['client_id'] : 56;
    $offset = ($page - 1) * $limit;
    
    // Simple query to test
    $query = "SELECT 
        id,
        item_title,
        Price,
        ImageFile,
        Category,
        FormID,
        CID
    FROM Items 
    WHERE CID = :client_id
    LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':client_id', $client_id);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = array();
    while ($row = $stmt->fetch()) {
        $image_url = '';
        if ($row['ImageFile']) {
            $image_url = $base_url . '/pdf/' . $row['CID'] . '/' . $row['ImageFile'];
        }
        
        $products[] = array(
            'id' => (int)$row['id'],
            'name' => $row['item_title'] ?: 'Product',
            'price' => (float)($row['Price'] ?: 0),
            'image_url' => $image_url,
            'category_id' => (int)($row['Category'] ?: 0),
            'sku' => $row['FormID'] ?: ''
        );
    }
    
    // Output clean JSON
    echo json_encode(array(
        'success' => true,
        'data' => $products
    ));
    
} catch (Exception $e) {
    // Return error as valid JSON
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage()
    ));
}
?>