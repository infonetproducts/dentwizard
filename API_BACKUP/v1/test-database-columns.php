<?php
// Database Column Test - Upload this to verify actual column names

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

$pdo = getPDOConnection();
$tables_info = array();

// Check Items table columns
try {
    $stmt = $pdo->query("DESCRIBE Items");
    $items_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tables_info['Items'] = $items_columns;
} catch (Exception $e) {
    $tables_info['Items'] = "Error: " . $e->getMessage();
}

// Check Category table columns  
try {
    $stmt = $pdo->query("DESCRIBE Category");
    $category_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tables_info['Category'] = $category_columns;
} catch (Exception $e) {
    $tables_info['Category'] = "Error: " . $e->getMessage();
}

// Check Users table columns
try {
    $stmt = $pdo->query("DESCRIBE Users");
    $users_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tables_info['Users'] = $users_columns;
} catch (Exception $e) {
    $tables_info['Users'] = "Error: " . $e->getMessage();
}

// Check FormCategoryLink table columns
try {
    $stmt = $pdo->query("DESCRIBE FormCategoryLink");
    $fcl_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tables_info['FormCategoryLink'] = $fcl_columns;
} catch (Exception $e) {
    $tables_info['FormCategoryLink'] = "Error: " . $e->getMessage();
}

// Get sample data from Items table
try {
    $stmt = $pdo->query("SELECT * FROM Items LIMIT 1");
    $sample_item = $stmt->fetch(PDO::FETCH_ASSOC);
    $tables_info['Sample_Item_Data'] = $sample_item;
} catch (Exception $e) {
    $tables_info['Sample_Item_Data'] = "Error: " . $e->getMessage();
}

// Get sample data from Category table
try {
    $stmt = $pdo->query("SELECT * FROM Category LIMIT 1");
    $sample_category = $stmt->fetch(PDO::FETCH_ASSOC);
    $tables_info['Sample_Category_Data'] = $sample_category;
} catch (Exception $e) {
    $tables_info['Sample_Category_Data'] = "Error: " . $e->getMessage();
}

echo json_encode($tables_info, JSON_PRETTY_PRINT);
?>