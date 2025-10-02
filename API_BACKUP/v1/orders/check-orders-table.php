<?php
// Check Orders table structure
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$servername = "localhost";
$username = "rwaf";
$password = "Py*uhb\$L\$##";
$dbname = "rwaf";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die(json_encode(array('error' => 'Connection failed')));
}

// Get Orders table columns
$query = "SHOW COLUMNS FROM Orders";
$result = mysqli_query($conn, $query);

$columns = array();
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = array(
        'field' => $row['Field'],
        'type' => $row['Type']
    );
}

// Find specific columns we need
$important_columns = array();
$column_names = array_column($columns, 'field');

// Check for different variations of column names
$checks = array(
    'order_id' => array('OrderID', 'order_id', 'OrderId'),
    'user_id' => array('UserID', 'user_id', 'UserId'),
    'order_date' => array('OrderDate', 'order_date'),
    'order_status' => array('OrderStatus', 'order_status'),
    'ship_name' => array('ShipToName', 'ship_name', 'ship_to_name'),
    'ship_add1' => array('ShipToAdd1', 'ship_add1', 'ship_to_add1'),
    'order_total' => array('OrderTOTAL', 'OrderTotal', 'order_total'),
    'shipping' => array('ShippingCost', 'shipping_charge', 'ShippingCharge'),
    'tax' => array('TotalSaleTax', 'total_sale_tax'),
    'payment' => array('PaymentMethod', 'payment_method')
);

foreach ($checks as $key => $variations) {
    foreach ($variations as $variant) {
        if (in_array($variant, $column_names)) {
            $important_columns[$key] = $variant;
            break;
        }
    }
}

echo json_encode(array(
    'total_columns' => count($columns),
    'important_columns_found' => $important_columns,
    'all_columns' => $columns
), JSON_PRETTY_PRINT);

mysqli_close($conn);
?>