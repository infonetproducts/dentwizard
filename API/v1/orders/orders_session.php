<?php
// Orders API - Works with both session and user_id parameter
// Matches your working system's authentication pattern

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// Get user_id - try multiple methods
$user_id = 0;

// Method 1: Check for user_id parameter (like create.php)
if (isset($_GET['user_id']) && intval($_GET['user_id']) > 0) {
    $user_id = intval($_GET['user_id']);
}
// Method 2: Check session (like budget.php)
elseif (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($user_id == 0 && isset($_SESSION['AID'])) {
    $user_id = intval($_SESSION['AID']);
}

// Method 3: Default to Joe Lorenzo for testing (user_id=19346)
if ($user_id == 0) {
    $user_id = 19346; // Joe Lorenzo's ID for testing
}

$client_id = 244; // DentWizard client ID

// Get orders for the user
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
        ORDER BY o.OrderDate DESC
        LIMIT 50";

$result = $mysqli->query($sql);

if (!$result) {
    die(json_encode(array(
        'status' => 'error', 
        'message' => 'Query failed',
        'user_id' => $user_id,
        'error' => $mysqli->error
    )));
}

$orders = array();

while ($order = $result->fetch_assoc()) {
    // Get items for each order
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
                $name_sql = "SELECT Name, item_title FROM Items WHERE ID = " . intval($item['ItemID']);
                $name_result = $mysqli->query($name_sql);
                if ($name_result && $name_row = $name_result->fetch_assoc()) {
                    $item['FormDescription'] = $name_row['item_title'] ? $name_row['item_title'] : $name_row['Name'];
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
    
    // Format order
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

// Add debug info to help troubleshoot
$response = array(
    'orders' => $orders,
    'debug' => array(
        'user_id' => $user_id,
        'total_orders' => count($orders),
        'session_aid' => isset($_SESSION['AID']) ? $_SESSION['AID'] : null,
        'get_user_id' => isset($_GET['user_id']) ? $_GET['user_id'] : null
    )
);

echo json_encode($orders); // Return just orders array for compatibility

$mysqli->close();
?>