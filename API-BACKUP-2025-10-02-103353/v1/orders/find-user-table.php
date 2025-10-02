<?php
// Find the actual user table
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

// Find all tables that might contain user data
$query = "SHOW TABLES";
$result = $mysqli->query($query);

$all_tables = array();
$user_tables = array();

if ($result) {
    while ($row = $result->fetch_array()) {
        $table_name = $row[0];
        $all_tables[] = $table_name;
        
        // Check if this might be a user table
        $lower = strtolower($table_name);
        if (strpos($lower, 'user') !== false || 
            strpos($lower, 'member') !== false || 
            strpos($lower, 'customer') !== false ||
            strpos($lower, 'account') !== false ||
            strpos($lower, 'employee') !== false ||
            strpos($lower, 'person') !== false ||
            strpos($lower, 'login') !== false) {
            $user_tables[] = $table_name;
        }
    }
}

// Check each potential user table for Jamie's data
$found_in = array();
foreach ($user_tables as $table) {
    // Check by ID
    $query1 = "SELECT * FROM $table WHERE ID = 20296 LIMIT 1";
    $result1 = @$mysqli->query($query1);
    if ($result1 && $result1->num_rows > 0) {
        $found_in[$table] = 'Found by ID 20296';
    }
    
    // Check by email if not found by ID
    if (!isset($found_in[$table])) {
        $query2 = "SELECT * FROM $table WHERE Email LIKE '%jkrugger%' LIMIT 1";
        $result2 = @$mysqli->query($query2);
        if ($result2 && $result2->num_rows > 0) {
            $found_in[$table] = 'Found by email';
        }
    }
    
    // Check columns if table exists
    if (!isset($found_in[$table])) {
        $query3 = "DESCRIBE $table";
        $result3 = @$mysqli->query($query3);
        if ($result3) {
            $cols = array();
            while ($col = $result3->fetch_assoc()) {
                $cols[] = $col['Field'];
            }
            // Check if it has user-like columns
            if (in_array('Email', $cols) || in_array('email', $cols)) {
                // Count records
                $count_query = "SELECT COUNT(*) as cnt FROM $table";
                $count_result = @$mysqli->query($count_query);
                if ($count_result) {
                    $count = $count_result->fetch_assoc()['cnt'];
                    $found_in[$table] = "Has Email column, $count records";
                }
            }
        }
    }
}

die(json_encode(array(
    'total_tables' => count($all_tables),
    'user_related_tables' => $user_tables,
    'found_jamie_in' => $found_in,
    'all_tables_sample' => array_slice($all_tables, 0, 20)
), JSON_PRETTY_PRINT));
?>