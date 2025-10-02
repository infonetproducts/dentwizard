<?php  
// PHP 5.6 COMPATIBLE - Categories List
// NO PHP 7 features

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

// Direct database connection
// ACTUAL DATABASE CREDENTIALS
$db_host = 'localhost';
$db_name = 'rwaf';
$db_user = 'rwaf';
$db_pass = 'Py*uhb$L$##';

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $client_id = isset($_GET['client_id']) ? $_GET['client_id'] : 56;
    
    $sql = "SELECT ID, Name, ParentID 
            FROM Category 
            WHERE CID = :cid AND Status = 'Y' 
            ORDER BY Name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':cid', $client_id);
    $stmt->execute();
    
    $categories = array();
    
    while ($row = $stmt->fetch()) {
        $categories[] = array(
            'id' => (int)$row['ID'],
            'name' => $row['Name'],
            'parent_id' => (int)$row['ParentID']
        );
    }
    
    echo json_encode(array(
        'success' => true,
        'data' => $categories
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'error' => 'Database connection failed'
    ));
}
?>