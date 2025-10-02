<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
set_time_limit(5);

$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$product_id = 83983;

// Get the product's FormID
$sql = "SELECT FormID FROM Items WHERE ID = $product_id LIMIT 1";
$result = $mysqli->query($sql);
$product = $result->fetch_assoc();

$formID = $product['FormID'];
$base_formid = substr($formID, 0, 8);

echo json_encode([
    'product_id' => $product_id,
    'formID' => $formID,
    'base_formid' => $base_formid,
    'testing' => 'Now testing the FormID LIKE query...'
]) . "\n\n";

// This is the query from detail.php line 103 that might be causing the problem
$formid_sql = "SELECT DISTINCT FormID 
               FROM Items 
               WHERE FormID LIKE '$base_formid%' 
               AND CID = 244 
               AND status_item = 'Y'
               LIMIT 100";

$start_time = microtime(true);
$formid_result = $mysqli->query($formid_sql);
$end_time = microtime(true);

if ($formid_result) {
    echo json_encode([
        'query_success' => true,
        'rows_returned' => $formid_result->num_rows,
        'execution_time' => round($end_time - $start_time, 3) . ' seconds',
        'query' => $formid_sql
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'query_failed' => true,
        'error' => $mysqli->error
    ]);
}

$mysqli->close();
?>
