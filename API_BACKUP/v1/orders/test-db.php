<?php
// Simple diagnostic check - PHP 5.6 compatible
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');

echo "Step 1: PHP is working\n";

// Check if we can find the config file
$config_path = '../config/db_connect.php';
if (file_exists($config_path)) {
    echo "Step 2: Config file found at: " . $config_path . "\n";
} else {
    echo "Step 2: ERROR - Config file NOT found at: " . $config_path . "\n";
    // Try alternative path
    $alt_path = '../../config/db_connect.php';
    if (file_exists($alt_path)) {
        echo "Found config at alternative path: " . $alt_path . "\n";
        $config_path = $alt_path;
    }
}

// Try to include the config
echo "Step 3: Attempting to include config...\n";
include_once($config_path);

// Check if connection exists
if (isset($conn)) {
    echo "Step 4: Database connection variable exists\n";
    
    // Try a simple query
    $test_query = "SELECT 1";
    $result = mysqli_query($conn, $test_query);
    
    if ($result) {
        echo "Step 5: Database query successful\n";
        
        // Check for OrderDetails table
        $table_query = "SHOW TABLES LIKE 'OrderDetails'";
        $table_result = mysqli_query($conn, $table_query);
        
        if ($table_result && mysqli_num_rows($table_result) > 0) {
            echo "Step 6: OrderDetails table EXISTS\n";
            
            // Get column count
            $col_query = "SHOW COLUMNS FROM OrderDetails";
            $col_result = mysqli_query($conn, $col_query);
            
            if ($col_result) {
                $num_cols = mysqli_num_rows($col_result);
                echo "Step 7: OrderDetails has " . $num_cols . " columns\n";
                
                echo "\nColumn names:\n";
                while ($row = mysqli_fetch_assoc($col_result)) {
                    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
                }
            } else {
                echo "Step 7: ERROR getting columns - " . mysqli_error($conn) . "\n";
            }
        } else {
            echo "Step 6: OrderDetails table NOT found\n";
            echo "Available tables:\n";
            $all_tables = mysqli_query($conn, "SHOW TABLES");
            while ($table = mysqli_fetch_array($all_tables)) {
                echo "- " . $table[0] . "\n";
            }
        }
    } else {
        echo "Step 5: Database query FAILED - " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Step 4: Database connection variable NOT found\n";
}

echo "\nDiagnostic complete.";
?>