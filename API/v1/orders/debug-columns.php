<?php
// Check what column names exist and find Joe's orders
header('Content-Type: application/json');

$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

// First, check column names in Orders table
$columns_sql = "SHOW COLUMNS FROM Orders";
$columns_result = $mysqli->query($columns_sql);
$columns = [];
while ($col = $columns_result->fetch_assoc()) {
    $columns[] = $col['Field'];
}

// Try different possible user ID columns
$user_id = 19346;
$possible_columns = ['UID', 'UserID', 'UserId', 'user_id', 'uid'];
$found_orders = false;
$working_column = null;

foreach ($possible_columns as $col) {
    if (in_array($col, $columns)) {
        $sql = "SELECT OrderID, OrderDate, Total FROM Orders WHERE $col = $user_id LIMIT 5";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            $found_orders = true;
            $working_column = $col;
            $orders = [];
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            break;
        }
    }
}

// Also check by email
$email_sql = "SELECT OrderID, OrderDate, Total FROM Orders WHERE CustomerEmail = 'joseph.lorenzo@dentwizard.com' LIMIT 5";
$email_result = $mysqli->query($email_sql);
$email_orders = [];
if ($email_result) {
    while ($row = $email_result->fetch_assoc()) {
        $email_orders[] = $row;
    }
}

echo json_encode([
    'table_columns' => $columns,
    'user_id_checked' => $user_id,
    'working_column' => $working_column,
    'found_orders' => $found_orders,
    'orders_by_id' => isset($orders) ? $orders : [],
    'orders_by_email' => $email_orders
]);

$mysqli->close();
?>