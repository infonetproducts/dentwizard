<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

// Test with a product from the products list that DOES work
// Let's use one of the first products visible
$test_products = [83983, 83984, 83985]; // Test multiple products

$results = [];

foreach ($test_products as $product_id) {
    $size_sql = "SELECT DISTINCT Size 
                 FROM ItemsSizesStyles 
                 WHERE ItemID = $product_id 
                 AND Size != '' 
                 AND Size IS NOT NULL
                 ORDER BY Size";
    
    $result = $mysqli->query($size_sql);
    
    $results[] = [
        'product_id' => $product_id,
        'query_success' => ($result !== false),
        'error' => $result === false ? $mysqli->error : null,
        'rows' => $result !== false ? $result->num_rows : null
    ];
}

echo json_encode(['test_results' => $results], JSON_PRETTY_PRINT);
$mysqli->close();
?>
