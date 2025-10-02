<?php
// Test database connection and check Orders table
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Database connection failed')));
}

// Check if Orders table exists
$result = $mysqli->query("SHOW TABLES LIKE 'Orders'");
$table_exists = ($result && $result->num_rows > 0);

// Count total orders
$total_orders = 0;
if ($table_exists) {
    $count_result = $mysqli->query("SELECT COUNT(*) as total FROM Orders");
    if ($count_result) {
        $row = $count_result->fetch_assoc();
        $total_orders = $row['total'];
    }
}

// Get sample orders (first 5)
$sample_orders = [];
if ($table_exists) {
    $sample_result = $mysqli->query("SELECT ID, OrderID, UserID, OrderDate, order_total FROM Orders LIMIT 5");
    if ($sample_result) {
        while ($order = $sample_result->fetch_assoc()) {
            $sample_orders[] = $order;
        }
    }
}

// Also check table structure
$columns = [];
if ($table_exists) {
    $cols_result = $mysqli->query("SHOW COLUMNS FROM Orders");
    if ($cols_result) {
        while ($col = $cols_result->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
    }
}

echo json_encode(array(
    'database_connected' => true,
    'orders_table_exists' => $table_exists,
    'total_orders' => $total_orders,
    'sample_orders' => $sample_orders,
    'table_columns' => $columns
));

$mysqli->close();
?>