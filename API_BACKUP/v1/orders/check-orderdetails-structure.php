<?php
// Check OrderDetails table structure - PHP 5.6 compatible
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once('../config/db_connect.php');

try {
    // Check if OrderDetails table exists
    $table_check_query = "SHOW TABLES LIKE 'OrderDetails'";
    $table_result = mysqli_query($conn, $table_check_query);
    
    if (!$table_result || mysqli_num_rows($table_result) == 0) {
        echo json_encode(array(
            'success' => false,
            'error' => 'OrderDetails table not found',
            'table_exists' => false
        ));
        exit();
    }
    
    // Get all columns from OrderDetails
    $columns_query = "SHOW COLUMNS FROM OrderDetails";
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
    
    // Check for size columns
    if (in_array('size', $columns)) {
        $attribute_columns_found['size'] = 'size';
    } elseif (in_array('size_item', $columns)) {
        $attribute_columns_found['size'] = 'size_item';
    }
    
    // Check for color columns  
    if (in_array('color', $columns)) {
        $attribute_columns_found['color'] = 'color';
    } elseif (in_array('color_item', $columns)) {
        $attribute_columns_found['color'] = 'color_item';
    }
    
    // Check for artwork/logo columns
    if (in_array('artwork', $columns)) {
        $attribute_columns_found['artwork'] = 'artwork';
    } elseif (in_array('artwork_logo', $columns)) {
        $attribute_columns_found['artwork'] = 'artwork_logo';
    }
    
    if (in_array('logo_option', $columns)) {
        $attribute_columns_found['logo_option'] = 'logo_option';
    }
    
    // Generate ALTER statements for missing columns
    $alter_statements = array();
    $columns_to_add = array();
    
    if (!isset($attribute_columns_found['size'])) {
        $alter_statements[] = "ALTER TABLE OrderDetails ADD COLUMN size VARCHAR(50);";
        $columns_to_add[] = 'size';
    }
    
    if (!isset($attribute_columns_found['color'])) {
        $alter_statements[] = "ALTER TABLE OrderDetails ADD COLUMN color VARCHAR(100);";
        $columns_to_add[] = 'color';
    }
    
    if (!isset($attribute_columns_found['artwork'])) {
        $alter_statements[] = "ALTER TABLE OrderDetails ADD COLUMN artwork VARCHAR(255);";
        $columns_to_add[] = 'artwork';
    }
    
    // Return the results
    echo json_encode(array(
        'success' => true,
        'table_exists' => true,
        'total_columns' => count($columns),
        'columns_list' => $columns,
        'attribute_columns_found' => $attribute_columns_found,
        'missing_columns' => $columns_to_add,
        'needs_alter' => !empty($alter_statements),
        'alter_sql_statements' => $alter_statements,
        'column_details' => $all_column_details
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'error' => 'Exception: ' . $e->getMessage()
    ));
}

mysqli_close($conn);
?>