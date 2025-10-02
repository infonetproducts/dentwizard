<?php
// Products Search API - IMPROVED VERSION WITH BETTER WORD MATCHING
error_reporting(0);
ini_set('display_errors', 0);

if (ob_get_level()) ob_end_clean();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

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
    
    // Build the WHERE conditions for search
    $searchConditions = array();
    $searchParams = array();
    
    if ($search) {
        // Create multiple search patterns for better matching
        $searchLower = strtolower($search);
        
        // Pattern 1: Exact match at beginning of title
        $searchConditions[] = "LOWER(i.item_title) LIKE :search_start";
        $searchParams['search_start'] = $searchLower . '%';
        
        // Pattern 2: Word match (with space before)
        $searchConditions[] = "LOWER(i.item_title) LIKE :search_word";
        $searchParams['search_word'] = '% ' . $searchLower . '%';
        
        // Pattern 3: Match in description as whole word
        $searchConditions[] = "LOWER(i.Description) LIKE :search_desc_word";
        $searchParams['search_desc_word'] = '% ' . $searchLower . ' %';
        
        // Pattern 4: Exact category match (if searching for "hat", "hats", etc.)
        if (in_array($searchLower, array('hat', 'hats', 'cap', 'caps', 'shirt', 'shirts', 'pant', 'pants'))) {
            $searchConditions[] = "LOWER(i.item_title) LIKE :search_category";
            $searchParams['search_category'] = '%' . $searchLower . '%';
        }
    }
    
    // Build query
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
                AND (" . implode(' OR ', $searchConditions) . ")
            LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':cid', $client_id);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        foreach ($searchParams as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
    } elseif ($category_id) {
        // Category only