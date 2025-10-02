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

echo '{"testing":"Sizes and Colors queries","results":[' . "\n";

// Test 1: Size query (line 87 in detail.php)
$size_sql = "SELECT DISTINCT Size 
             FROM ItemsSizesStyles 
             WHERE ItemID = $product_id 
             AND Size != '' 
             AND Size IS NOT NULL
             ORDER BY Size";

$start = microtime(true);
$size_result = $mysqli->query($size_sql);
$time = round(microtime(true) - $start, 3);

if ($size_result === false) {
    echo json_encode(['test' => 'sizes', 'error' => $mysqli->error, 'query' => $size_sql]);
    die(']}');
}

echo json_encode(['test' => 'sizes', 'success' => true, 'rows' => $size_result->num_rows, 'time' => $time . 's']) . ",\n";

// Test 2: Color query (line 50 in detail.php)
$color_sql = "SELECT option_id, display_name, value, color_image 
              FROM item_group_options 
              WHERE item_id = $product_id 
              AND CID = 244 
              AND price = 0
              ORDER BY option_id";

$start = microtime(true);
$color_result = $mysqli->query($color_sql);
$time = round(microtime(true) - $start, 3);

if ($color_result === false) {
    echo json_encode(['test' => 'colors', 'error' => $mysqli->error, 'query' => $color_sql]);
    die(']}');
}

echo json_encode(['test' => 'colors', 'success' => true, 'rows' => $color_result->num_rows, 'time' => $time . 's']);

echo "\n]}";

$mysqli->close();
?>
