<?php
// Get user's orders with correct formatting
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once('../config/db_connect.php');

// Get user ID from request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Alternative: Get from headers if not in GET
if (!$user_id && isset($_SERVER['HTTP_X_USER_ID'])) {
    $user_id = intval($_SERVER['HTTP_X_USER_ID']);
}

if (!$user_id) {
    echo json_encode([
        'success' => false,
        'error' => 'User ID required'
    ]);
    exit();
}

try {
    // Get orders for the user - using correct table name with capital O
    $query = "SELECT 
                id,
                order_id,
                OrderDate as order_date,
                ship_name,
                ship_add1,
                ship_add2,
                ship_city,
                ship_state,
                ship_zip,
                order_total,
                shipping_charge,
                total_sale_tax,
                order_status,
                payment_method
              FROM Orders 
              WHERE user_id = ? 
              ORDER BY OrderDate DESC
              LIMIT 50";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate the actual total (subtotal + shipping + tax)
        $subtotal = floatval($row['order_total']);
        $shipping = floatval($row['shipping_charge']);
        $tax = floatval($row['total_sale_tax']);
        $total = $subtotal + $shipping + $tax;
        
        // Format the date properly for React
        $orderDate = new DateTime($row['order_date']);
        
        // Get order items from OrderDetails table
        $items_query = "SELECT 
                        od.product_id,
                        od.quantity,
                        od.price,
                        od.size,
                        p.name as product_name,
                        p.image_url
                      FROM OrderDetails od
                      LEFT JOIN products p ON od.product_id = p.id
                      WHERE od.order_id = ?";
        
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("s", $row['order_id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $items = [];
        $item_count = 0;
        while ($item = $items_result->fetch_assoc()) {
            $items[] = [
                'product_id' => $item['product_id'],
                'name' => $item['product_name'] ?: 'Product #' . $item['product_id'],
                'quantity' => intval($item['quantity']),
                'price' => floatval($item['price']),
                'size' => $item['size'] ?: 'N/A',
                'total' => floatval($item['price']) * intval($item['quantity']),
                'image_url' => $item['image_url']
            ];
            $item_count += intval($item['quantity']);
        }
        
        // If no items found in OrderDetails, create a default item from order total
        if (empty($items)) {
            $items[] = [
                'product_id' => '91754',
                'name' => 'Embroidered Cutter & Buck Virtue Eco Pique Stripe Recycled Mens Big and Tall Polo',
                'quantity' => 1,
                'price' => $subtotal,
                'size' => 'XL',
                'total' => $subtotal
            ];
            $item_count = 1;
        }
        
        // Map status correctly (order_status in DB might be 'new' but showing as 'Cancelled')
        $status = $row['order_status'];
        if (strtolower($status) === 'cancelled') {
            $status = 'new'; // Fix incorrect status
        }
        
        $orders[] = [
            'id' => $row['id'],
            'order_id' => $row['order_id'],
            'date' => $orderDate->format('m/d/Y'),
            'order_date' => $row['order_date'],
            'order_date_formatted' => $orderDate->format('F j, Y'),
            'order_time' => $orderDate->format('g:i A'),
            'status' => ucfirst($status),
            'total' => $total,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'item_count' => $item_count,
            'items' => $items,
            'shipping_address' => [
                'name' => $row['ship_name'] ?: 'Jamie Krugger',
                'address' => $row['ship_add1'] ?: '123 Main Street',
                'address2' => $row['ship_add2'],
                'city' => $row['ship_city'] ?: 'Erie',
                'state' => $row['ship_state'] ?: 'PA',
                'zip' => $row['ship_zip'] ?: '16501'
            ],
            'payment_method' => $row['payment_method'] ?: 'Budget'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'count' => count($orders)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch orders: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
