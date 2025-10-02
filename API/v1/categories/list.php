<?php
// FINAL WORKING VERSION - Categories List API
// Using CORRECT column names from database

error_reporting(0);
ini_set('display_errors', 0);

if (ob_get_level()) ob_end_clean();

require_once '../../cors.php';
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
    
    $client_id = isset($_GET['client_id']) ? $_GET['client_id'] : 244;
    
    // CORRECT column names: ID, Name, ParentID, Active, CID
    $sql = "SELECT 
            ID, 
            Name, 
            ParentID,
            display_type
        FROM Category 
        WHERE CID = :cid 
            AND Active = 'Y' 
        ORDER BY display_order, Name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':cid', $client_id);
    $stmt->execute();
    
    $categories = array();
    
    while ($row = $stmt->fetch()) {
        $categories[] = array(
            'id' => (int)$row['ID'],
            'name' => $row['Name'],
            'parent_id' => (int)$row['ParentID'],
            'display_type' => (int)$row['display_type']
        );
    }
    
    echo json_encode(array(
        'success' => true,
        'data' => $categories
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'error' => 'Database error'
    ));
}
?>