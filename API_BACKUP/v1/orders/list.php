<?php
// api/v1/orders/list.php
// Get user's order history

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Validate authentication
AuthMiddleware::validateRequest();
$client_id = $GLOBALS['auth_user']['client_id'];
$user_id = $GLOBALS['auth_user']['id'];

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

$pdo = getPDOConnection();
$base_url = getenv('BASE_URL') ?: 'https://your-php-server.com';

// Get total count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM Orders WHERE UID = :user_id AND CID = :client_id");
$stmt->execute([
    'user_id' => $user_id,
    'client_id' => $client_id
]);
$total = $stmt->fetch()['total'];

// Get orders
$stmt = $pdo->prepare("
    SELECT 
        OrderID as order_id,
        OrderNumber as order_number,
        CreatedDate as date,
        Status as status,
        Total as total,
        PaymentMethod as payment_method
    FROM Orders 
    WHERE UID = :user_id AND CID = :client_id
    ORDER BY CreatedDate DESC
    LIMIT :limit OFFSET :offset
");

$stmt->execute([
    'user_id' => $user_id,
    'client_id' => $client_id,
    'limit' => $limit,
    'offset' => $offset
]);

$orders = [];
while ($order = $stmt->fetch()) {
    // Get items count for this order
    $item_stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(Quantity) as total_items 
                                FROM OrderItems WHERE OrderID = :order_id");
    $item_stmt->execute(['order_id' => $order['order_id']]);
    $item_data = $item_stmt->fetch();
    
    // Get first few items for preview
    $items_stmt = $pdo->prepare("
        SELECT ItemTitle as product_name, Quantity as quantity, Price as price
        FROM OrderItems 
        WHERE OrderID = :order_id
        LIMIT 3
    ");
    $items_stmt->execute(['order_id' => $order['order_id']]);
    $items_preview = $items_stmt->fetchAll();
    
    // Add product images if available
    foreach ($items_preview as &$item) {
        // Try to get image from Items table
        $img_stmt = $pdo->prepare("SELECT ImageFile FROM Items WHERE item_title = :title LIMIT 1");
        $img_stmt->execute(['title' => $item['product_name']]);
        $img = $img_stmt->fetch();
        
        if ($img && $img['ImageFile']) {
            $item['image'] = $base_url . '/pdf/' . $client_id . '/' . $img['ImageFile'];
        } else {
            $item['image'] = null;
        }
    }
    
    $orders[] = [
        'order_id' => $order['order_id'],
        'order_number' => $order['order_number'],
        'date' => $order['date'],
        'status' => $order['status'],
        'total' => (float)$order['total'],
        'items_count' => (int)$item_data['total_items'],
        'payment_method' => $order['payment_method'],
        'items' => $items_preview
    ];
}

// Return response
echo json_encode([
    'success' => true,
    'data' => [
        'orders' => $orders,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_orders' => (int)$total
        ]
    ]
]);
?>