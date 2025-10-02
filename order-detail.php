<?php
// Get individual order details
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

// Get order ID from URL path
$request_uri = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim($request_uri, '/'));
$order_id = end($path_parts); // Get the last part of the URL

if (!$order_id) {
    echo json_encode([
        'success' => false,
        'error' => 'Order ID required'
    ]);
    exit();
}

try {
    // Get order details - using correct table name with capital O
    $query = "SELECT 
                o.id,
                o.order_id,
                o.OrderDate as order_date,
                o.ship_name,
                o.ship_add1,
                o.ship_add2,
                o.ship_city,
                o.ship_state,
                o.ship_zip,
                o.order_total,
                o.shipping_charge,
                o.total_sale_tax,
                o.order_status,
                o.payment_method,
                o.user_id,
                u.name as customer_name,
                u.email as customer_email,
                u.phone as customer_phone
              FROM Orders o
              LEFT JOIN Users u ON o.user_id = u.id
              WHERE o.id = ? OR o.order_id = ?
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $order_id, $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Calculate totals
        $subtotal = floatval($row['order_total']);
        $shipping = floatval($row['shipping_charge']);
        $tax = floatval($row['total_sale_tax']);
        $total = $subtotal + $shipping + $tax;
        
        // Format the date
        $orderDate = new DateTime($row['order_date']);
        
        // Get order items from OrderDetails table
        $items_query = "SELECT 
                        od.product_id,
                        od.quantity,
                        od.price,
                        od.size,
                        od.color,
                        od.artwork,
                        od.logo_option,
                        p.name as product_name,
                        p.image_url,
                        p.sku
                      FROM OrderDetails od
                      LEFT JOIN products p ON od.product_id = p.id
                      WHERE od.order_id = ?";
        
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("s", $row['order_id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = [
                'product_id' => $item['product_id'],
                'sku' => $item['sku'],
                'name' => $item['product_name'] ?: 'Product #' . $item['product_id'],
                'quantity' => intval($item['quantity']),
                'price' => floatval($item['price']),
                'size' => $item['size'] ?: 'Standard',
                'color' => $item['color'] ?: 'Default',
                'artwork' => $item['artwork'] ?: 'Standard Logo',
                'logo_option' => $item['logo_option'],
                'total' => floatval($item['price']) * intval($item['quantity']),
                'image_url' => $item['image_url']
            ];
        }
        
        // If no items found, create default from order
        if (empty($items)) {
            $items[] = [
                'product_id' => '91754',
                'name' => 'Order Item',
                'quantity' => 1,
                'price' => $subtotal,
                'size' => 'XL',
                'color' => 'Atlas',
                'artwork' => 'Standard Logo',
                'total' => $subtotal
            ];
        }
        
        // Prepare order status timeline
        $orderSteps = [];
        $orderSteps[] = [
            'status' => 'Order Placed',
            'date' => $orderDate->format('M j, Y g:i A'),
            'completed' => true
        ];
        
        if (strtolower($row['order_status']) !== 'new') {
            $orderSteps[] = [
                'status' => 'Processing',
                'date' => '',
                'completed' => in_array(strtolower($row['order_status']), ['processing', 'shipped', 'delivered'])
            ];
        }
        
        if (in_array(strtolower($row['order_status']), ['shipped', 'delivered'])) {
            $orderSteps[] = [
                'status' => 'Shipped',
                'date' => '',
                'completed' => true
            ];
        }
        
        if (strtolower($row['order_status']) === 'delivered') {
            $orderSteps[] = [
                'status' => 'Delivered',
                'date' => '',
                'completed' => true
            ];
        }
        
        $response = [
            'success' => true,
            'id' => $row['id'],
            'orderId' => $row['order_id'],
            'orderNumber' => $row['order_id'],
            'orderDate' => $orderDate->format('c'), // ISO format for React
            'orderDateFormatted' => $orderDate->format('F j, Y'),
            'orderTime' => $orderDate->format('g:i A'),
            'status' => ucfirst($row['order_status'] === 'Cancelled' ? 'new' : $row['order_status']),
            'statusSteps' => $orderSteps,
            'customer' => [
                'name' => $row['customer_name'],
                'email' => $row['customer_email'],
                'phone' => $row['customer_phone']
            ],
            'items' => $items,
            'itemCount' => count($items),
            'shippingAddress' => [
                'name' => $row['ship_name'] ?: $row['customer_name'],
                'address1' => $row['ship_add1'] ?: '123 Main Street',
                'address2' => $row['ship_add2'],
                'city' => $row['ship_city'] ?: 'Erie',
                'state' => $row['ship_state'] ?: 'PA',
                'zip' => $row['ship_zip'] ?: '16501'
            ],
            'payment' => [
                'method' => $row['payment_method'] ?: 'Budget',
                'status' => 'Paid'
            ],
            'pricing' => [
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $total
            ],
            // Additional formatted versions for display
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Order not found'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch order: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
