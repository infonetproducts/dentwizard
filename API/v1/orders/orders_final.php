<?php
// Orders API - Exactly matching the working detail.php pattern
// Deploy to: /lg/API/v1/orders.php or /lg/API/v1/orders/my-orders.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get user_id parameter - same as create.php
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 19346; // Default for testing

// Database connection - same pattern as detail.php
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

$client_id = 244; // DentWizard client ID - same as create.php and detail.php

// Check what endpoint is being called
$request_uri = $_SERVER['REQUEST_URI'];

// Handle my-orders endpoint
if (strpos($request_uri, 'my-orders') !== false) {
    
    // Get orders for the user - matching detail.php query style
    $sql = "SELECT 
                o.ID,
                o.OrderID,
                o.OrderDate,
                o.Status,
                o.order_total,
                o.total_sale_tax,
                o.ShipToName,
                o.tracking_number,
                o.custom_desc
            FROM Orders o
            WHERE o.UserID = $user_id
            AND o.CID = $client_id
            ORDER BY o.OrderDate DESC
            LIMIT 50";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        die(json_encode(array('status' => 'error', 'message' => 'Query failed')));
    }    
    $orders = array();
    
    while ($order = $result->fetch_assoc()) {
        // Get items for each order - matching detail.php pattern
        $order_id = intval($order['ID']);
        
        $item_sql = "SELECT 
                        oi.ItemID,
                        oi.FormDescription,
                        oi.Quantity,
                        oi.Price,
                        oi.Size,
                        oi.Color
                    FROM OrderItems oi
                    WHERE oi.OrderRecordID = $order_id";
        
        $item_result = $mysqli->query($item_sql);
        $items = array();
        $subtotal = 0;
        
        if ($item_result && $item_result->num_rows > 0) {
            while ($item = $item_result->fetch_assoc()) {
                // Get item name from Items table if FormDescription is empty
                if (empty($item['FormDescription']) && !empty($item['ItemID'])) {
                    $name_sql = "SELECT Name FROM Items WHERE ID = " . intval($item['ItemID']);
                    $name_result = $mysqli->query($name_sql);
                    if ($name_result && $name_row = $name_result->fetch_assoc()) {
                        $item['FormDescription'] = $name_row['Name'];
                    }
                }                
                $items[] = array(
                    'product_id' => intval($item['ItemID']),
                    'product_name' => $item['FormDescription'],
                    'quantity' => intval($item['Quantity']),
                    'price' => floatval($item['Price']),
                    'size' => $item['Size'],
                    'color' => $item['Color']
                );
                
                $subtotal += floatval($item['Price']) * intval($item['Quantity']);
            }
        }
        
        // Format order like detail.php formats products
        $formatted_order = array(
            'id' => intval($order['ID']),
            'order_number' => $order['OrderID'],
            'created_at' => $order['OrderDate'],
            'status' => strtolower($order['Status'] ?: 'pending'),
            'total' => floatval($order['order_total']),
            'tax' => floatval($order['total_sale_tax']),
            'subtotal' => $subtotal,
            'ship_to_name' => $order['ShipToName'],
            'tracking_number' => $order['tracking_number'],
            'description' => $order['custom_desc'],
            'items' => $items
        );
        
        $orders[] = $formatted_order;
    }    
    // Return in same format as detail.php
    echo json_encode($orders);
    
} else {
    // Handle single order detail by ID
    $order_id = 0;
    
    // Check for order ID in URL
    if (preg_match('/\/orders\/(\d+)/', $request_uri, $matches)) {
        $order_id = intval($matches[1]);
    } elseif (isset($_GET['id'])) {
        $order_id = intval($_GET['id']);
    }
    
    if ($order_id <= 0) {
        die(json_encode(array('status' => 'error', 'message' => 'Invalid order ID')));
    }
    
    // Get the order details - matching detail.php pattern
    $sql = "SELECT * FROM Orders 
            WHERE ID = $order_id 
            AND UserID = $user_id 
            AND CID = $client_id 
            LIMIT 1";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        die(json_encode(array('status' => 'error', 'message' => 'Query failed')));
    }    
    $order = $result->fetch_assoc();
    
    if (!$order) {
        die(json_encode(array('status' => 'error', 'message' => 'Order not found')));
    }
    
    // Get order items
    $item_sql = "SELECT 
                    oi.ItemID,
                    oi.FormDescription,
                    oi.Quantity,
                    oi.Price,
                    oi.Size,
                    oi.Color,
                    i.SKU,
                    i.Name
                FROM OrderItems oi
                LEFT JOIN Items i ON oi.ItemID = i.ID
                WHERE oi.OrderRecordID = $order_id";
    
    $item_result = $mysqli->query($item_sql);
    $items = array();
    $subtotal = 0;
    
    if ($item_result && $item_result->num_rows > 0) {
        while ($item = $item_result->fetch_assoc()) {
            $product_name = !empty($item['FormDescription']) ? $item['FormDescription'] : $item['Name'];
            
            $items[] = array(                'product_id' => intval($item['ItemID']),
                'product_name' => $product_name,
                'quantity' => intval($item['Quantity']),
                'price' => floatval($item['Price']),
                'size' => $item['Size'],
                'color' => $item['Color'],
                'sku' => $item['SKU']
            );
            
            $subtotal += floatval($item['Price']) * intval($item['Quantity']);
        }
    }
    
    // Format the complete response - matching detail.php structure
    $response = array(
        'status' => 'success',
        'data' => array(
            'id' => intval($order['ID']),
            'order_number' => $order['OrderID'],
            'created_at' => $order['OrderDate'],
            'status' => strtolower($order['Status'] ?: 'pending'),
            'total' => floatval($order['order_total']),
            'tax' => floatval($order['total_sale_tax']),
            'subtotal' => $subtotal,
            'shipping_cost' => floatval($order['shipping_charge']),
            'items' => $items,
            'shipping_address' => array(
                'name' => $order['ShipToName'],
                'address' => $order['Address1'],
                'address2' => $order['Address2'],
                'city' => $order['City'],
                'state' => $order['State'],
                'zip' => $order['Zip'],
                'phone' => $order['Phone']
            ),
            'notes' => $order['Notes'],
            'tracking_number' => $order['tracking_number'],
            'shipped_date' => $order['ShipDate'],
            'delivered_date' => $order['item_due_date']
        )
    );
    
    echo json_encode($response);
}

$mysqli->close();
?>