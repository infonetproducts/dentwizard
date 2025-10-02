<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
set_time_limit(5);

$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$product_id = 91754;

// Test the size query
$size_sql = "SELECT DISTINCT Size 
             FROM ItemsSizesStyles 
             WHERE ItemID = $product_id 
             AND Size != '' 
             AND Size IS NOT NULL
             ORDER BY Size";

$start = microtime(true);
$result = $mysqli->query($size_sql);
$time = round(microtime(true) - $start, 3);

if ($result === false) {
    echo json_encode([
        'product_id' => $product_id,
        'table_exists' => false,
        'error' => $mysqli->error,
        'conclusion' => 'ItemsSizesStyles table does not exist - should affect ALL products'
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'product_id' => $product_id,
        'table_exists' => true,
        'size_query_success' => true,
        'rows_found' => $result->num_rows,
        'execution_time' => $time . 's',
        'conclusion' => 'Query works for product ' . $product_id
    ], JSON_PRETTY_PRINT);
}

$mysqli->close();
?>
