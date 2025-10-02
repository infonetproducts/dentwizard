<?php
// DATABASE DOCUMENTATION GENERATOR
// Upload as: database-info.php
// Run: https://dentwizard.lgstore.com/lg/API/v1/database-info.php
// This will document your entire database structure

header("Access-Control-Allow-Origin: *");
header("Content-Type: text/plain; charset=utf-8");

// Database credentials
$db_host = 'localhost';
$db_name = 'rwaf';
$db_user = 'rwaf';
$db_pass = 'Py*uhb$L$##';

echo "===========================================\n";
echo "DATABASE STRUCTURE DOCUMENTATION\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8",
        $db_user,
        $db_pass,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
    echo "✓ Database connection successful\n";
    echo "Database: $db_name\n\n";
    
    // Get all tables
    echo "===========================================\n";
    echo "ALL TABLES IN DATABASE\n";
    echo "===========================================\n";
    $tables_query = $pdo->query("SHOW TABLES");
    $all_tables = $tables_query->fetchAll(PDO::FETCH_COLUMN);
    foreach ($all_tables as $idx => $table) {
        echo ($idx + 1) . ". $table\n";
    }
    echo "\nTotal tables: " . count($all_tables) . "\n\n";
    
    // Key tables we need to examine
    $important_tables = array('Items', 'Category', 'Users', 'Orders', 'FormCategoryLink');
    
    foreach ($important_tables as $table) {
        echo "===========================================\n";
        echo "TABLE: $table\n";
        echo "===========================================\n";
        
        // Check if table exists
        if (!in_array($table, $all_tables)) {
            echo "⚠ TABLE DOES NOT EXIST\n";
            // Try to find similar table names
            echo "Similar tables found:\n";
            foreach ($all_tables as $t) {
                if (stripos($t, substr($table, 0, 4)) !== false) {
                    echo "  - $t\n";
                }
            }
            echo "\n";
            continue;
        }
        
        // Get column information
        echo "\nCOLUMNS:\n";
        echo "--------\n";
        $columns_query = $pdo->query("SHOW COLUMNS FROM `$table`");
        $columns = $columns_query->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $col) {
            echo sprintf("%-20s | Type: %-20s | Null: %-3s | Key: %-3s | Default: %s\n",
                $col['Field'],
                $col['Type'],
                $col['Null'],
                $col['Key'],
                $col['Default'] ?: 'NULL'
            );
        }
        
        // Get row count
        $count_query = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $count_query->fetchColumn();
        echo "\nTotal rows: $count\n";
        
        // Get sample data
        if ($count > 0) {
            echo "\nSAMPLE DATA (First 2 rows):\n";
            echo "------------------------\n";
            $sample_query = $pdo->query("SELECT * FROM `$table` LIMIT 2");
            $samples = $sample_query->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($samples as $idx => $row) {
                echo "Row " . ($idx + 1) . ":\n";
                foreach ($row as $key => $value) {
                    // Truncate long values
                    if (strlen($value) > 50) {
                        $value = substr($value, 0, 50) . "...";
                    }
                    echo "  $key: $value\n";
                }
                echo "\n";
            }
        }
        echo "\n";
    }
    
    // Special queries to understand relationships
    echo "===========================================\n";
    echo "SPECIAL QUERIES FOR API\n";
    echo "===========================================\n\n";
    
    // Check for product-related tables
    echo "Product-related tables:\n";
    foreach ($all_tables as $table) {
        if (stripos($table, 'item') !== false || stripos($table, 'product') !== false) {
            echo "  - $table\n";
        }
    }
    echo "\n";
    
    // Check for category-related tables
    echo "Category-related tables:\n";
    foreach ($all_tables as $table) {
        if (stripos($table, 'categ') !== false || stripos($table, 'cat') !== false) {
            echo "  - $table\n";
        }
    }
    echo "\n";
    
    // Try to find the main products table
    echo "Attempting to identify main products table:\n";
    $possible_product_tables = array('Items', 'items', 'Products', 'products', 'Item', 'Product');
    foreach ($possible_product_tables as $table) {
        if (in_array($table, $all_tables)) {
            echo "  ✓ Found: $table\n";
            
            // Check for key columns
            $check_cols = array('title', 'name', 'price', 'Price', 'image', 'ImageFile');
            $cols_query = $pdo->query("SHOW COLUMNS FROM `$table`");
            $cols = $cols_query->fetchAll(PDO::FETCH_COLUMN);
            echo "    Key columns found:\n";
            foreach ($check_cols as $col) {
                foreach ($cols as $actual_col) {
                    if (stripos($actual_col, $col) !== false) {
                        echo "      - $actual_col (matches $col)\n";
                    }
                }
            }
            break;
        }
    }
    
    echo "\n===========================================\n";
    echo "SUMMARY\n";
    echo "===========================================\n";
    echo "Database connection: OK\n";
    echo "Total tables: " . count($all_tables) . "\n";
    echo "Key tables examined: " . implode(', ', $important_tables) . "\n";
    
} catch (Exception $e) {
    echo "===========================================\n";
    echo "ERROR\n";
    echo "===========================================\n";
    echo "Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. Database credentials are correct\n";
    echo "2. Database server is running\n";
    echo "3. User has proper permissions\n";
}

echo "\n===========================================\n";
echo "END OF REPORT\n";
echo "===========================================\n";
?>