<?php
/**
 * Database Structure Verification Script
 * This script checks if your database has the expected structure for the API
 * 
 * Usage: php test-database.php
 * Or visit: http://your-server.com/API/test-database.php
 */

header('Content-Type: text/plain');

echo "===========================================\n";
echo "   DentWizard Database Structure Test\n";
echo "===========================================\n\n";

// Load configuration
if (!file_exists('.env')) {
    die("Error: .env file not found. Please create it from .env.example\n");
}

$env = parse_ini_file('.env');

try {
    // Connect to database
    $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8";
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASSWORD'] ?? '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connection successful\n\n";
} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

$errors = 0;
$warnings = 0;

// Define expected table structures
$tables = [
    'Users' => [
        'required_fields' => ['ID', 'CID', 'Name', 'Email'],
        'optional_fields' => ['BillCode', 'BudgetBalance', 'is_view_only', 'Phone', 'Company']
    ],
    'Items' => [
        'required_fields' => ['ID', 'CID', 'FormID', 'item_title', 'Price'],
        'optional_fields' => ['Description', 'ImageFile', 'status_item', 'sale_tax', 'is_apply_sale_tax']
    ],
    'Orders' => [
        'required_fields' => ['OrderID', 'UID', 'CID'],
        'optional_fields' => ['OrderNumber', 'Status', 'Total', 'CreatedDate']
    ],
    'OrderItems' => [
        'required_fields' => ['OrderID', 'ItemID'],
        'optional_fields' => ['Quantity', 'Price', 'FormID']
    ],
    'Category' => [
        'required_fields' => ['ID', 'CID', 'Name'],
        'optional_fields' => ['ParentID', 'Status', 'display_type', 'sort_order']
    ],
    'Clients' => [
        'required_fields' => ['ID', 'Name'],
        'optional_fields' => ['is_enable_sale', 'percentage_off', 'shop_template']
    ],
    'FormCategoryLink' => [
        'required_fields' => ['FormID', 'CategoryID'],
        'optional_fields' => []
    ]
];

// Check each table
foreach ($tables as $table => $structure) {
    echo "Checking table: $table\n";
    echo str_repeat('-', 40) . "\n";
    
    // Check if table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    
    if ($stmt->rowCount() === 0) {
        echo "✗ Table '$table' does not exist\n";
        $errors++;
        echo "\n";
        continue;
    }
    
    echo "✓ Table exists\n";
    
    // Get table columns
    $stmt = $pdo->prepare("DESCRIBE $table");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Check required fields
    echo "  Required fields:\n";
    foreach ($structure['required_fields'] as $field) {
        if (in_array($field, $columns)) {
            echo "    ✓ $field\n";
        } else {
            echo "    ✗ $field (missing)\n";
            $errors++;
        }
    }
    
    // Check optional fields
    if (!empty($structure['optional_fields'])) {
        echo "  Optional fields:\n";
        foreach ($structure['optional_fields'] as $field) {
            if (in_array($field, $columns)) {
                echo "    ✓ $field\n";
            } else {
                echo "    ⚠ $field (not found - may affect some features)\n";
                $warnings++;
            }
        }
    }
    
    // Get row count
    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
    $count = $stmt->fetchColumn();
    echo "  Row count: $count\n";
    
    if ($count === 0) {
        echo "  ⚠ Table is empty\n";
        $warnings++;
    }
    
    echo "\n";
}

// Check for additional tables that might be needed
echo "Checking additional tables:\n";
echo str_repeat('-', 40) . "\n";

$additionalTables = [
    'items_size_price' => 'Size pricing options',
    'items_color_options' => 'Color options',
    'items_range_price' => 'Quantity-based pricing',
    'product_images' => 'Additional product images',
    'multiple_shipping_address' => 'Saved shipping addresses',
    'logos' => 'Logo options for products'
];

foreach ($additionalTables as $table => $purpose) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ $table - $purpose\n";
    } else {
        echo "⚠ $table - Not found ($purpose)\n";
        $warnings++;
    }
}

echo "\n";

// Test sample queries
echo "Testing sample queries:\n";
echo str_repeat('-', 40) . "\n";

// Test 1: Get a user
try {
    $stmt = $pdo->query("SELECT ID, Name, Email FROM Users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "✓ Can retrieve users\n";
    } else {
        echo "⚠ No users found in database\n";
        $warnings++;
    }
} catch (PDOException $e) {
    echo "✗ Error retrieving users: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 2: Get products with categories
try {
    $stmt = $pdo->query("
        SELECT i.ID, i.item_title, c.Name as category_name
        FROM Items i
        LEFT JOIN FormCategoryLink fcl ON fcl.FormID = i.ID
        LEFT JOIN Category c ON c.ID = fcl.CategoryID
        LIMIT 1
    ");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        echo "✓ Can retrieve products with categories\n";
    } else {
        echo "⚠ No products found or category linking issue\n";
        $warnings++;
    }
} catch (PDOException $e) {
    echo "✗ Error retrieving products: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 3: Check for client
try {
    $stmt = $pdo->query("SELECT ID, Name FROM Clients LIMIT 1");
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($client) {
        echo "✓ Can retrieve clients\n";
        echo "  First client: ID={$client['ID']}, Name={$client['Name']}\n";
    } else {
        echo "✗ No clients found - This will cause API failures\n";
        $errors++;
    }
} catch (PDOException $e) {
    echo "✗ Error retrieving clients: " . $e->getMessage() . "\n";
    $errors++;
}

echo "\n";

// Summary
echo "===========================================\n";
echo "                 SUMMARY\n";
echo "===========================================\n";
echo "Errors:   $errors\n";
echo "Warnings: $warnings\n\n";

if ($errors === 0) {
    echo "✓ Database structure is compatible with the API!\n";
    
    if ($warnings > 0) {
        echo "\nSome optional features may not work due to missing tables/fields.\n";
        echo "This is normal if you don't use those features.\n";
    }
} else {
    echo "✗ Database structure has issues that need to be fixed.\n";
    echo "\nRequired fixes:\n";
    echo "- Add any missing required fields to tables\n";
    echo "- Ensure all required tables exist\n";
    echo "- Check that table names match exactly (case-sensitive)\n";
}

echo "\n";

// Display database info
echo "Database Information:\n";
echo str_repeat('-', 40) . "\n";
echo "Host:     {$env['DB_HOST']}\n";
echo "Database: {$env['DB_NAME']}\n";
echo "User:     {$env['DB_USER']}\n";

// Get MySQL version
$version = $pdo->query("SELECT VERSION()")->fetchColumn();
echo "MySQL:    $version\n";

// Get database size
$stmt = $pdo->prepare("
    SELECT 
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
    FROM information_schema.tables 
    WHERE table_schema = ?
");
$stmt->execute([$env['DB_NAME']]);
$size = $stmt->fetchColumn();
echo "Size:     {$size} MB\n";

echo "\n===========================================\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
?>