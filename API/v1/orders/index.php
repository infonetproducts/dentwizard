<?php
// api/v1/orders/index.php
// Orders endpoint using the same auth system as profile.php

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Require authentication (same as profile.php)
AuthMiddleware::validateRequest();
$user_id = $GLOBALS['auth_user']['id'];
$client_id = $GLOBALS['auth_user']['client_id'];

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/lg/API/v1/orders/';
$endpoint = str_replace($base_path, '', parse_url($request_uri, PHP_URL_PATH));

$pdo = getPDOConnection();

try {
    switch($endpoint) {
        case 'my-orders':
        case 'index.php/my-orders':
            // Fetch user's orders
            $stmt = $pdo->prepare("
                SELECT 
                    OrderID as id,
                    CONCAT('DW-', DATE_FORMAT(OrderDate, '%Y%m%d'), '-', LPAD(OrderID, 4, '0')) as orderNumber,
                    OrderDate as createdAt,                    OrderStatus as status,
                    Total as total,
                    ShipToName as shippingName,
                    ShipToAddress1 as shippingAddress
                FROM Orders 
                WHERE UID = :user_id 
                ORDER BY OrderDate DESC
            ");
            $stmt->execute(array('user_id' => $user_id));
            $orders = $stmt->fetchAll();
            
            echo json_encode(array(
                'success' => true,
                'data' => $orders
            ));
            break;
            
        default:
            // Check if it's a numeric ID for order details
            if (is_numeric($endpoint)) {
                $order_id = (int)$endpoint;
                
                // Fetch specific order
                $stmt = $pdo->prepare("
                    SELECT 
                        OrderID as id,
                        CONCAT('DW-', DATE_FORMAT(OrderDate, '%Y%m%d'), '-', LPAD(OrderID, 4, '0')) as orderNumber,
                        OrderDate as createdAt,                        OrderStatus as status,
                        Total as total,
                        ShipToName as shippingName,
                        ShipToAddress1 as shippingAddress,
                        ShipToCity as shippingCity,
                        ShipToState as shippingState,
                        ShipToZip as shippingZip
                    FROM Orders 
                    WHERE OrderID = :order_id 
                    AND UID = :user_id
                ");
                $stmt->execute(array(
                    'order_id' => $order_id,
                    'user_id' => $user_id
                ));
                $order = $stmt->fetch();
                
                if (!$order) {
                    http_response_code(404);
                    echo json_encode(array(
                        'success' => false,
                        'error' => 'Order not found'
                    ));
                    exit;
                }
                
                // Fetch order items
                $stmt = $pdo->prepare("
                    SELECT 
                        OrderItemID as id,                        ProductID as productId,
                        ProductName as name,
                        Quantity as quantity,
                        Price as price
                    FROM OrderItems 
                    WHERE OrderID = :order_id
                ");
                $stmt->execute(array('order_id' => $order_id));
                $items = $stmt->fetchAll();
                
                $order['items'] = $items;
                
                echo json_encode(array(
                    'success' => true,
                    'data' => $order
                ));
            } else {
                http_response_code(404);
                echo json_encode(array(
                    'success' => false,
                    'error' => 'Invalid endpoint'
                ));
            }
            break;
    }
    
} catch (Exception $e) {
    error_log('Orders endpoint error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to retrieve orders'
    ));
}
?>