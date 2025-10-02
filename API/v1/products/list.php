<?php
// Products List API - WITH CATEGORY FILTERING
error_reporting(0);
ini_set('display_errors', 0);

if (ob_get_level()) ob_end_clean();

// Include centralized CORS configuration
require_once __DIR__ . '/../../cors.php';

// Set content type
header("Content-Type: application/json");

// Database connection
$db_host = 'localhost';
$db_name = 'rwaf';
$db_user = 'rwaf';
$db_pass = 'Py*uhb$L$##';

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $client_id = isset($_GET['client_id']) ? $_GET['client_id'] : 244;
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $offset = ($page - 1) * $limit;
    
    // Build query based on whether category filter and/or search is applied
    if ($category_id && $search) {
        // Both category and search
        $sql = "SELECT DISTINCT
                i.ID,
                i.item_title, 
                i.Price, 
                i.ImageFile, 
                i.FormID,
                i.CID,
                i.Description,
                i.status_item
            FROM Items i
            INNER JOIN FormCategoryLink fcl ON fcl.FormID = i.ID
            WHERE i.CID = :cid 
                AND i.status_item = 'Y'
                AND fcl.CategoryID = :category_id
                AND (
                    i.item_title LIKE :search_start 
                    OR i.item_title LIKE :search_word
                    OR i.item_title LIKE :search
                )
            LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':cid', $client_id);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':search_start', $search . '%');
        $stmt->bindValue(':search_word', '% ' . $search . '%');
        $stmt->bindValue(':search', '%' . $search . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
    } elseif ($category_id) {
        // Filter by category only
        // FormCategoryLink.FormID links to Items.ID (not Items.FormID!)
        $sql = "SELECT DISTINCT
                i.ID,
                i.item_title, 
                i.Price, 
                i.ImageFile, 
                i.FormID,
                i.CID,
                i.Description,
                i.status_item
            FROM Items i
            INNER JOIN FormCategoryLink fcl ON fcl.FormID = i.ID
            WHERE i.CID = :cid 
                AND i.status_item = 'Y'
                AND fcl.CategoryID = :category_id
            LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':cid', $client_id);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
    } elseif ($search) {
        // Search only (no category filter)
        $sql = "SELECT 
                ID,
                item_title, 
                Price, 
                ImageFile, 
                FormID,
                CID,
                Description,
                status_item
            FROM Items 
            WHERE CID = :cid 
                AND status_item = 'Y'
                AND (
                    item_title LIKE :search_start 
                    OR item_title LIKE :search_word
                    OR item_title LIKE :search
                )
            LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':cid', $client_id);
        $stmt->bindValue(':search_start', $search . '%');
        $stmt->bindValue(':search_word', '% ' . $search . '%');
        $stmt->bindValue(':search', '%' . $search . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
    } else {
        // No category filter or search - show all products
        $sql = "SELECT 
                ID,
                item_title, 
                Price, 
                ImageFile, 
                FormID,
                CID,
                Description,
                status_item
            FROM Items 
            WHERE CID = :cid 
                AND status_item = 'Y'
            LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':cid', $client_id);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    $products = array();
    
    while ($row = $stmt->fetch()) {
        $image_url = '';
        if (!empty($row['ImageFile'])) {
            $image_url = 'https://dentwizard.lgstore.com/pdf/' . $row['CID'] . '/' . $row['ImageFile'];
        }
        
        // Get actual category ID for this product
        $cat_stmt = $pdo->prepare("SELECT CategoryID FROM FormCategoryLink WHERE FormID = :item_id LIMIT 1");
        $cat_stmt->execute(array(':item_id' => $row['ID']));
        $cat_result = $cat_stmt->fetch();
        $product_category_id = $cat_result ? $cat_result['CategoryID'] : 0;
        
        $products[] = array(
            'id' => (int)$row['ID'],
            'name' => !empty($row['item_title']) ? $row['item_title'] : 'Product',
            'price' => !empty($row['Price']) ? (float)$row['Price'] : 0,
            'image_url' => $image_url,
            'category_id' => (int)$product_category_id,
            'sku' => !empty($row['FormID']) ? $row['FormID'] : '',
            'description' => !empty($row['Description']) ? $row['Description'] : ''
        );
    }
    
    // Get total count for pagination
    if ($category_id) {
        $count_sql = "SELECT COUNT(DISTINCT i.ID) as total
                      FROM Items i
                      INNER JOIN FormCategoryLink fcl ON fcl.FormID = i.ID
                      WHERE i.CID = :cid 
                          AND i.status_item = 'Y'
                          AND fcl.CategoryID = :category_id";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->bindValue(':cid', $client_id);
        $count_stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
    } else {
        $count_sql = "SELECT COUNT(*) as total
                      FROM Items
                      WHERE CID = :cid AND status_item = 'Y'";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->bindValue(':cid', $client_id);
    }
    
    $count_stmt->execute();
    $total_result = $count_stmt->fetch();
    $total_count = (int)$total_result['total'];
    
    echo json_encode(array(
        'success' => true,
        'data' => $products,
        'pagination' => array(
            'page' => $page,
            'limit' => $limit,
            'total' => $total_count,
            'total_pages' => ceil($total_count / $limit)
        ),
        'filter' => array(
            'category_id' => $category_id,
            'client_id' => $client_id
        )
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ));
}
?>