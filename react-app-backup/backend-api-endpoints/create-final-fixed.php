<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

$mysqli->set_charset("utf8");

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // React sends 'user_id' not 'userId'
    $userId = intval($data['user_id']);
    $items = $data['items'];
    $shippingAddress = $data['shippingAddress'];
    
    // React sends subtotal, shippingCost, tax
    $subtotal = floatval($data['subtotal']);
    $tax = floatval($data['tax']);
    $shippingCost = floatval($data['shippingCost']);
    $totalAmount = floatval($data['total']);
    
    if (empty($userId) || empty($items)) {
        throw new Exception('Missing required fields');
    }
    
    $mysqli->begin_transaction();
    
    // Generate unique order number
    $orderNumber = date('md-His') . '-' . $userId;
    
    // Insert order - React sends 'address' not 'address1'
    $shippingName = $mysqli->real_escape_string($shippingAddress['name']);
    $shippingAddress1 = $mysqli->real_escape_string($shippingAddress['address']);
    $shippingAddress2 = $mysqli->real_escape_string(isset($shippingAddress['address2']) ? $shippingAddress['address2'] : '');
    $shippingCity = $mysqli->real_escape_string($shippingAddress['city']);
    $shippingState = $mysqli->real_escape_string($shippingAddress['state']);
    $shippingZip = $mysqli->real_escape_string($shippingAddress['zip']);
    $shippingCountry = '';
    
    $orderQuery = "INSERT INTO Orders (
        OrderID, UserID, OrderDate, order_total, Status,
        ShipToName, Address1, Address2,
        City, State, Zip, country,
        shipping_charge, total_sale_tax
    ) VALUES (
        '$orderNumber', $userId, NOW(), $totalAmount, 'pending',
        '$shippingName', '$shippingAddress1', '$shippingAddress2',
        '$shippingCity', '$shippingState', '$shippingZip', '$shippingCountry',
        $shippingCost, $tax
    )";
    
    if (!$mysqli->query($orderQuery)) {
        throw new Exception('Failed to create order: ' . $mysqli->error);
    }
    
    $orderId = $mysqli->insert_id;
    
    // Insert order items with attributes
    foreach ($items as $item) {
        $productId = intval($item['id']);
        $productName = $mysqli->real_escape_string($item['name']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        
        // Extract product attributes - these are the critical fields
        $sizeItem = isset($item['size']) ? $mysqli->real_escape_string($item['size']) : '';
        $colorItem = isset($item['color']) ? $mysqli->real_escape_string($item['color']) : '';
        $artworkLogo = isset($item['artwork']) ? $mysqli->real_escape_string($item['artwork']) : '';
        
        $itemQuery = "INSERT INTO OrderItems (
            FormID, ItemID, FormDescription, Quantity, Price,
            size_item, color_item, artwork_logo
        ) VALUES (
            '$orderNumber', $productId, '$productName', $quantity, $price,
            '$sizeItem', '$colorItem', '$artworkLogo'
        )";
        
        if (!$mysqli->query($itemQuery)) {
            throw new Exception('Failed to add order item: ' . $mysqli->error);
        }
    }
    
    // Update user budget
    $updateBudgetQuery = "UPDATE Users 
                         SET BudgetBalance = BudgetBalance - $totalAmount 
                         WHERE ID = $userId";
    
    if (!$mysqli->query($updateBudgetQuery)) {
        throw new Exception('Failed to update budget: ' . $mysqli->error);
    }
    
    $mysqli->commit();
    
    echo json_encode(array(
        'success' => true,
        'message' => 'Order created successfully',
        'orderId' => $orderId,
        'orderNumber' => $orderNumber
    ));
    
} catch (Exception $e) {
    if (isset($mysqli)) {
        $mysqli->rollback();
    }
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

if (isset($mysqli)) {
    $mysqli->close();
}
?>