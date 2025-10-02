<?php
// Test how products are linked to categories
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$db_host = 'localhost';
$db_name = 'rwaf';
$db_user = 'rwaf';
$db_pass = 'Py*uhb$L$##';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check FormCategoryLink structure
    $stmt = $pdo->query("DESCRIBE FormCategoryLink");
    $fcl_structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get sample data from FormCategoryLink
    $stmt2 = $pdo->query("SELECT * FROM FormCategoryLink WHERE FormID IN (SELECT ID FROM Items WHERE CID = 244 LIMIT 10) LIMIT 10");
    $fcl_samples = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // Try alternative - FormID might link to Items.ID
    $stmt3 = $pdo->query("
        SELECT fcl.*, i.FormID as item_formid, i.item_title
        FROM FormCategoryLink fcl
        JOIN Items i ON i.ID = fcl.FormID
        WHERE i.CID = 244
        LIMIT 10
    ");
    $linked_samples = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    // Check what categories have products
    $stmt4 = $pdo->query("
        SELECT c.Name, COUNT(DISTINCT fcl.FormID) as product_count
        FROM Category c
        LEFT JOIN FormCategoryLink fcl ON fcl.CategoryID = c.ID
        WHERE c.CID = 244 AND c.Active = 'Y'
        GROUP BY c.ID, c.Name
        HAVING product_count > 0
        LIMIT 20
    ");
    $categories_with_products = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(array(
        'fcl_structure' => $fcl_structure,
        'fcl_samples' => $fcl_samples,
        'linked_samples' => $linked_samples,
        'categories_with_products' => $categories_with_products
    ), JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
}
?>