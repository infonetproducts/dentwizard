<?php
// CRITICAL: Suppress PHP errors that break JSON
error_reporting(0);
ini_set('display_errors', 0);

// CORS Headers FIRST
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$db_host = 'localhost';
$db_name = 'your_database_name';  // REPLACE with actual database name
$db_user = 'your_username';       // REPLACE with actual username  
$db_pass = 'your_password';       // REPLACE with actual password

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $client_id = isset($_GET['client_id']) ? $_GET['client_id'] : 56;
    
    $stmt = $pdo->prepare("SELECT * FROM Category WHERE CID = :client_id AND Status = 'Y' LIMIT 50");
    $stmt->execute(['client_id' => $client_id]);
    
    $categories = [];
    while ($row = $stmt->fetch()) {
        $categories[] = [
            'id' => (int)$row['ID'],
            'name' => $row['Name'],
            'parent_id' => (int)$row['ParentID']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $categories]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>