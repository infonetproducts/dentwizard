<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Product 83983 Debug Report</h1>";
echo "<hr>";

try {
    echo "<h2>Step 1: Loading database config...</h2>";
    
    // Try to load database config
    $db_file = __DIR__ . '/../config/database.php';
    echo "Looking for database.php at: " . $db_file . "<br>";
    
    if (!file_exists($db_file)) {
        echo "<b style='color:red'>ERROR: database.php not found!</b><br>";
        echo "Current directory: " . __DIR__ . "<br>";
        exit;
    }
    
    require_once $db_file;
    echo "<span style='color:green'>✓ Database config loaded</span><br><br>";
    
    echo "<h2>Step 2: Connecting to database...</h2>";
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<b style='color:red'>ERROR: Failed to connect to database</b><br>";
        exit;
    }
    echo "<span style='color:green'>✓ Database connected</span><br><br>";
    
    echo "<h2>Step 3: Querying product 83983...</h2>";
    
    $query = "SELECT * FROM items WHERE id = 83983";
    echo "Query: " . $query . "<br>";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo "<b style='color:red'>ERROR: Product 83983 not found in database!</b><br>";
        exit;
    }
    
    echo "<span style='color:green'>✓ Product found</span><br><br>";
    
    echo "<h2>Product Data:</h2>";
    echo "<pre>";
    print_r($product);
    echo "</pre>";
    
    echo "<h2>Step 4: Checking item_logo_ids field...</h2>";
    echo "item_logo_ids value: <b>" . ($product['item_logo_ids'] ?? 'NULL') . "</b><br>";
    echo "Type: " . gettype($product['item_logo_ids']) . "<br><br>";
    
    if (!empty($product['item_logo_ids'])) {
        echo "<h2>Step 5: Testing logo query...</h2>";
        
        $logo_query = "SELECT id, item_logo_type FROM item_logos WHERE id IN (" . $product['item_logo_ids'] . ")";
        echo "Logo query: <code>" . $logo_query . "</code><br>";
        
        try {
            $logo_stmt = $conn->prepare($logo_query);
            $logo_stmt->execute();
            $logos = $logo_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<span style='color:green'>✓ Logo query executed successfully</span><br>";
            echo "Found " . count($logos) . " logos<br><br>";
            
            echo "<h2>Logo Data:</h2>";
            echo "<pre>";
            print_r($logos);
            echo "</pre>";
            
        } catch (Exception $e) {
            echo "<b style='color:red'>ERROR in logo query: " . $e->getMessage() . "</b><br>";
        }
    } else {
        echo "<b style='color:orange'>No logos configured for this product</b><br>";
    }
    
    echo "<hr>";
    echo "<h2 style='color:green'>✓ Debug Complete</h2>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red'>FATAL ERROR</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
