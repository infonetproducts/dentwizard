<?php
// My Orders API - Fixed for proper display

// Include centralized CORS configuration
require_once __DIR__ . '/../../cors.php';

// Set content type
header("Content-Type: application/json");

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

// Get authentication from headers or body
function getUserIdFromRequest() {
    // Try headers first (for production)
    foreach ($_SERVER as $key => $value) {
        if ($key == 'HTTP_X_USER_ID') {
            return $value;
        }
    }
    
    // Try GET parameter as fallback
    if (isset($_GET['user_id'])) {
        return $_GET['user_id'];
    }
    
    return null;
}

$user_id = getUserIdFromRequest();

if (!$user_id) {
    die(json_encode(array('success' => false, 'message' => 'User ID required')));
}

// Get orders for user - using correct table name Orders
$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM OrderItems WHERE OrderRecordID = o.ID) as item_count,
          (SELECT SUM(Price * Quantity) FROM OrderItems WHERE OrderRecordID = o.ID) as calculated_total
          FROM Orders o 
          WHERE o.UserID = " . intval($user_id) . " 
          ORDER BY o.OrderDate DESC 
          LIMIT 50";

$result = $mysqli->query($query);

$orders = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Calculate the total - check different possible columns
        $order_total = 0;
        if (isset($row['order_total']) && $row['order_total'] > 0) {
            $order_total = floatval($row['order_total']);
        } elseif (isset($row['calculated_total']) && $row['calculated_total'] > 0) {
            $order_total = floatval($row['calculated_total']);
        } elseif (isset($row['OrderTotal']) && $row['OrderTotal'] > 0) {
            $order_total = floatval($row['OrderTotal']);
        }
        
        // Format the date properly
        $order_date = $row['OrderDate'];
        if ($order_date && $order_date != '0000-00-00 00:00:00') {
            $formatted_date = date('m/d/Y', strtotime($order_date));
        } else {
            $formatted_date = date('m/d/Y');
        }
        
        // Get order items for this order
        $items = array();
        $items_query = "SELECT * FROM OrderItems WHERE OrderRecordID = " . $row['ID'];
        $items_result = $mysqli->query($items_query);
        
        if ($items_result && $items_result->num_rows > 0) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = array(
                    'product_id' => $item['ItemID'],
                    'name' => $item['FormDescription'],
                    'quantity' => intval($item['Quantity']),
                    'price' => floatval($item['Price']),
                    'total' => floatval($item['Price']) * intval($item['Quantity']),
                    'size' => isset($item['size_item']) ? $item['size_item'] : '',
                    'color' => isset($item['color_item']) ? $item['color_item'] : '',
                    'logo' => isset($item['artwork_logo']) ? $item['artwork_logo'] : ''
                );
                
                // If order total was 0, calculate from items
                if ($order_total == 0) {
                    $order_total += floatval($item['Price']) * intval($item['Quantity']);
                }
            }
        }
        
        // Get shipping and tax separately (don't add to total if order_total already set)
        $shipping_cost = 0;
        if (isset($row['shipping_charge']) && $row['shipping_charge'] > 0) {
            $shipping_cost = floatval($row['shipping_charge']);
        } elseif (isset($row['ShipCost']) && $row['ShipCost'] > 0) {
            $shipping_cost = floatval($row['ShipCost']);
        }
        
        $tax = isset($row['total_sale_tax']) && $row['total_sale_tax'] > 0 ? floatval($row['total_sale_tax']) : 0;
        
        // If order_total wasn't set, add shipping
        if ($order_total > 0 && !isset($row['order_total'])) {
            $order_total += $shipping_cost;
        }
        
        // Format payment method for display
        $payment_method = isset($row['PaymentMethod']) ? $row['PaymentMethod'] : 'Credit Card';
        if (strtolower($payment_method) === 'budget') {
            $payment_method = 'Budget Balance';
        }
        
        // Build order array
        $orders[] = array(
            'id' => $row['ID'],
            'order_id' => $row['OrderID'],
            'date' => $formatted_date,
            'order_date' => $order_date,
            'status' => ucfirst($row['Status']),
            'total' => $order_total,
            'subtotal' => $order_total, // For compatibility
            'item_count' => intval($row['item_count']),
            'items' => $items,
            'shipping_address' => array(
                'name' => isset($row['ShipToName']) ? $row['ShipToName'] : $row['Name'],
                'address' => $row['Address1'],
                'city' => $row['City'],
                'state' => $row['State'],
                'zip' => $row['Zip']
            ),
            'payment_method' => $payment_method,
            'shipping_cost' => $shipping_cost,
            'tax' => $tax
        );
    }
}

// Return response
die(json_encode(array(
    'success' => true,
    'orders' => $orders,
    'count' => count($orders)
)));
?>