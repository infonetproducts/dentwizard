<?php
// Check OrderDetails table structure - Direct connection version
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection - same as working create.php
$servername = "localhost";
$username = "logostor_lg";
$password = "@dmin1234ros3";
$dbname = "logostor_lg";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    echo json_encode(array(
        'success' => false,
        'error' => 'Connection failed: ' . mysqli_connect_error()
    ));
    exit();
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

try {
    // Check if OrderDetails table exists
    $table_check_query = "SHOW TABLES LIKE 'OrderDetails'";
    $table_result = mysqli_query($conn, $table_check_query);
    
    if (!$table_result || mysqli_num_rows($table_result) == 0) {
        // Try with different table names
        $alternative_names = array('orderdetails', 'order_details', 'OrderItems', 'orderitems');
        $found_table = false;
        $actual_table_name = '';
        
        foreach ($alternative_names as $table_name) {
            $check_query = "SHOW TABLES LIKE '$table_name'";
            $check_result = mysqli_query($conn, $check_query);
            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $found_table = true;
                $actual_table_name = $table_name;
                break;
            }
        }
        
        if (!$found_table) {
            // List all tables
            $all_tables_query = "SHOW TABLES";
            $all_tables_result = mysqli_query($conn, $all_tables_query);
            $tables_list = array();
            
            while ($table_row = mysqli_fetch_array($all_tables_result)) {
                $tables_list[] = $table_row[0];
            }
            
            echo json_encode(array(
                'success' => false,
                'error' => 'OrderDetails table not found',
                'tables_checked' => array_merge(array('OrderDetails'), $alternative_names),
                'all_tables_in_database' => $tables_list,
                'suggestion' => 'Looking for order detail/items table'
            ));
            exit();
        } else {
            $table_to_check = $actual_table_name;
        }
    } else {
        $table_to_check = 'OrderDetails';
    }
    
    // Get all columns from the found table
    $columns_query = "SHOW COLUMNS FROM $table_to_check";
    $columns_result = mysqli_query($conn, $columns_query);
    
    if (!$columns_result) {
        echo json_encode(array(
            'success' => false,
            'error' => 'Failed to get columns: ' . mysqli_error($conn)
        ));
        exit();
    }
    
    $columns = array();
    $all_column_details = array();
    
    while ($row = mysqli_fetch_assoc($columns_result)) {
        $columns[] = $row['Field'];
        $all_column_details[] = array(
            'field' => $row['Field'],
            'type' => $row['Type'],
            'null' => $row['Null'],
            'key' => $row['Key'],
            'default' => $row['Default']
        );
    }
    
    // Check for product attribute columns
    $attribute_columns_found = array();
    
    // Check for size columns (size, size_item, Size, etc.)
    foreach ($columns as $col) {
        $col_lower = strtolower($col);
        if (strpos($col_lower, 'size') !== false) {
            $attribute_columns_found['size'] = $col;
        }
        if (strpos($col_lower, 'color') !== false || strpos($col_lower, 'colour') !== false) {
            $attribute_columns_found['color'] = $col;
        }
        if (strpos($col_lower, 'artwork') !== false || strpos($col_lower, 'logo') !== false) {
            $attribute_columns_found['artwork'] = $col;
        }
    }
    
    // Generate recommendations
    $recommendations = array();
    if (!isset($attribute_columns_found['size'])) {
        $recommendations[] = "No size column found - may need to add 'size' or 'size_item' column";
    }
    if (!isset($attribute_columns_found['color'])) {
        $recommendations[] = "No color column found - may need to add 'color' or 'color_item' column";
    }
    if (!isset($attribute_columns_found['artwork'])) {
        $recommendations[] = "No artwork/logo column found - may need to add 'artwork_logo' column";
    }
    
    // Return the results
    echo json_encode(array(
        'success' => true,
        'table_name' => $table_to_check,
        'total_columns' => count($columns),
        'columns_list' => $columns,
        'attribute_columns_found' => $attribute_columns_found,
        'recommendations' => $recommendations,
        'all_column_details' => $all_column_details
    ), JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'error' => 'Exception: ' . $e->getMessage()
    ));
}

mysqli_close($conn);
?>