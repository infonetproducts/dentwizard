<?php
// Diagnostic - Check for duplicate categories
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$db_host = 'localhost';
$db_name = 'rwaf';
$db_user = 'rwaf';
$db_pass = 'Py*uhb$L$##';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check for duplicate category names for client 244
    $sql = "SELECT Name, COUNT(*) as count, GROUP_CONCAT(ID) as ids
            FROM Category 
            WHERE CID = 244 AND Active = 'Y' AND ParentID = 0
            GROUP BY Name
            HAVING count > 1
            ORDER BY count DESC";
    
    $stmt = $pdo->query($sql);
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all categories for client 244
    $sql2 = "SELECT ID, Name, ParentID 
             FROM Category 
             WHERE CID = 244 AND Active = 'Y' AND ParentID = 0
             ORDER BY Name";
    
    $stmt2 = $pdo->query($sql2);
    $all_categories = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(array(
        'duplicate_categories' => $duplicates,
        'all_parent_categories' => $all_categories,
        'total_categories' => count($all_categories)
    ), JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
}
?>