<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

// Get all table names that might be related to sizes
$result = $mysqli->query("SHOW TABLES LIKE '%size%' OR SHOW TABLES LIKE '%item%'");

// Since we can't do OR in SHOW TABLES, let's get all tables and filter
$result = $mysqli->query("SHOW TABLES");

$tables = [];
while ($row = $result->fetch_array()) {
    $table_name = $row[0];
    // Look for tables with "size" or "item" in the name
    if (stripos($table_name, 'size') !== false || stripos($table_name, 'item') !== false) {
        $tables[] = $table_name;
    }
}

echo json_encode([
    'tables_with_size_or_item' => $tables,
    'looking_for' => 'The correct table for product sizes'
], JSON_PRETTY_PRINT);

$mysqli->close();
?>
