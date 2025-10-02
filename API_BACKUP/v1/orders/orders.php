<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

// Set headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Get the endpoint from the URL
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/lg/API/v1/orders/';
$endpoint = str_replace($base_path, '', $request_uri);
$endpoint = explode('?', $endpoint)[0]; // Remove query parameters

// Check if user is logged in
if (!isset($_SESSION['AID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = intval($_SESSION['AID']);

// Connect to database
$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
// Route based on endpoint
switch ($endpoint) {
    case 'my-orders':
        // Fetch orders for the logged-in user
        $sql = "SELECT 
                    OrderID as id,
                    CONCAT('DW-', DATE_FORMAT(OrderDate, '%Y%m%d'), '-', LPAD(OrderID, 4, '0')) as orderNumber,
                    OrderDate as createdAt,
                    OrderStatus as status,
                    OrderTotal as total,
                    ShippingName as shippingName,
                    ShippingAddress as shippingAddress
                FROM Orders 
                WHERE UserID = $user_id 
                ORDER BY OrderDate DESC";
        
        $result = $mysqli->query($sql);
        
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Query failed']);
            exit;
        }
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        echo json_encode($orders);
        break;
    default:
        // Check if it's an order ID (numeric endpoint)
        if (is_numeric($endpoint)) {
            $order_id = intval($endpoint);
            
            // Fetch order details
            $sql = "SELECT 
                        o.OrderID as id,
                        CONCAT('DW-', DATE_FORMAT(o.OrderDate, '%Y%m%d'), '-', LPAD(o.OrderID, 4, '0')) as orderNumber,
                        o.OrderDate as createdAt,
                        o.OrderStatus as status,
                        o.OrderTotal as total,
                        o.ShippingName as shippingName,
                        o.ShippingAddress as shippingAddress,
                        o.ShippingCity as shippingCity,
                        o.ShippingState as shippingState,
                        o.ShippingZip as shippingZip
                    FROM Orders o
                    WHERE o.OrderID = $order_id AND o.UserID = $user_id";
            
            $result = $mysqli->query($sql);
            
            if (!$result || $result->num_rows == 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
                exit;
            }
            
            $order = $result->fetch_assoc();
            // Fetch order items
            $items_sql = "SELECT 
                            oi.OrderItemID as id,
                            oi.ProductID as productId,
                            oi.ProductName as name,
                            oi.Quantity as quantity,
                            oi.Price as price
                        FROM OrderItems oi
                        WHERE oi.OrderID = $order_id";
            
            $items_result = $mysqli->query($items_sql);
            $items = [];
            
            if ($items_result) {
                while ($item = $items_result->fetch_assoc()) {
                    $items[] = $item;
                }
            }
            
            $order['items'] = $items;
            echo json_encode($order);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Invalid endpoint']);
        }
        break;
}

$mysqli->close();
?>