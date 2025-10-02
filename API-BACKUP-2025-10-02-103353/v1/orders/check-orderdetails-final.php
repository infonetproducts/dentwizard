<?php
// Check OrderDetails table structure - with correct database credentials
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Correct database connection from database-info.php
$servername = "localhost";
$username = "rwaf";
$password = "Py*uhb\$L\$##";
$dbname = "rwaf";

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
        // Try with different table names (from original PHP: OrderItems)
        $alternative_names = array('OrderItems', 'orderitems', 'order_details', 'order_items');
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
            // List all order-related tables
            $all_tables_query = "SHOW TABLES";
            $all_tables_result = mysqli_query($conn, $all_tables_query);
            $tables_list = array();
            $order_related = array();
            
            while ($table_row = mysqli_fetch_array($all_tables_result)) {
                $table_name = $table_row[0];
                $tables_list[] = $table_name;
                if (stripos($table_name, 'order') !== false) {
                    $order_related[] = $table_name;
                }
            }
            
            echo json_encode(array(
                'success' => false,
                'error' => 'OrderDetails/OrderItems table not found',
                'tables_checked' => array_merge(array('OrderDetails'), $alternative_names),
                'order_related_tables' => $order_related,
                'note' => 'Original PHP system uses OrderItems table'
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
    
    // Check for product attribute columns (based on original PHP: size_item, color_item, artwork_logo)
    $attribute_columns_found = array();
    $attribute_mapping = array();
    
    // Check for each attribute type
    foreach ($columns as $col) {
        // Size columns
        if ($col === 'size_item' || $col === 'size' || stripos($col, 'size') !== false) {
            $attribute_columns_found['size'] = $col;
            $attribute_mapping['size'] = $col;
        }
        // Color columns  
        if ($col === 'color_item' || $col === 'color' || stripos($col, 'color') !== false || stripos($col, 'colour') !== false) {
            $attribute_columns_found['color'] = $col;
            $attribute_mapping['color'] = $col;
        }
        // Artwork/Logo columns
        if ($col === 'artwork_logo' || $col === 'artwork' || stripos($col, 'artwork') !== false || stripos($col, 'logo') !== false) {
            $attribute_columns_found['artwork'] = $col;
            $attribute_mapping['artwork'] = $col;
        }
    }
    
    // Generate ALTER statements if columns are missing
    $alter_statements = array();
    $columns_to_add = array();
    
    // Based on original PHP, we need: size_item, color_item, artwork_logo
    if (!isset($attribute_columns_found['size'])) {
        $alter_statements[] = "ALTER TABLE $table_to_check ADD COLUMN size_item VARCHAR(50);";
        $columns_to_add[] = 'size_item';
    }
    
    if (!isset($attribute_columns_found['color'])) {
        $alter_statements[] = "ALTER TABLE $table_to_check ADD COLUMN color_item VARCHAR(100);";
        $columns_to_add[] = 'color_item';
    }
    
    if (!isset($attribute_columns_found['artwork'])) {
        $alter_statements[] = "ALTER TABLE $table_to_check ADD COLUMN artwork_logo VARCHAR(255);";
        $columns_to_add[] = 'artwork_logo';
    }
    
    // Get sample data if table has records
    $sample_data = array();
    $sample_query = "SELECT * FROM $table_to_check LIMIT 2";
    $sample_result = mysqli_query($conn, $sample_query);
    
    if ($sample_result && mysqli_num_rows($sample_result) > 0) {
        while ($sample_row = mysqli_fetch_assoc($sample_result)) {
            $sample_data[] = $sample_row;
        }
    }
    
    // Return comprehensive results
    echo json_encode(array(
        'success' => true,
        'table_found' => $table_to_check,
        'original_php_uses' => 'OrderItems with: size_item, color_item, artwork_logo',
        'total_columns' => count($columns),
        'columns_list' => $columns,
        'attribute_columns_found' => $attribute_columns_found,
        'attribute_mapping' => $attribute_mapping,
        'missing_columns' => $columns_to_add,
        'needs_alter' => !empty($alter_statements),
        'alter_sql_statements' => $alter_statements,
        'sample_data' => $sample_data,
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