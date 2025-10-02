<?php
// Test endpoint that returns EXACTLY what React expects
// This mimics the working profile.php structure

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Require authentication
AuthMiddleware::validateRequest();
$user_id = $GLOBALS['auth_user']['id'];

// Hardcode for testing - if auth doesn't give us Joe's ID, use it
if (!$user_id) {
    $user_id = 19346; // Joe's ID
}

$pdo = getPDOConnection();

try {
    // Simple query - just get the orders
    $stmt = $pdo->prepare("
        SELECT 
            OrderID as id,
            OrderDate as createdAt,
            Status as status,
            order_total as total
        FROM Orders 
        WHERE UserID = 19346
        ORDER BY OrderDate DESC
    ");
    
    $stmt->execute();
    $orders = $stmt->fetchAll();
    
    // Return exactly like React expects - plain array
    header('Content-Type: application/json');
    echo json_encode($orders);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>