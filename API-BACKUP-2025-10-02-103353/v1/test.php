<?php
// api/v1/test.php
// PHP 5.6 Compatible Test Endpoint


require_once '../config/cors.php';
require_once '../config/database.php';

// PHP Version Check
$php_version = PHP_VERSION;
$php_check = version_compare($php_version, '5.6.0', '>=') ? 'PASS' : 'FAIL';

// Extension Checks
$extensions = array(
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'json' => extension_loaded('json'),
    'curl' => extension_loaded('curl'),
    'session' => extension_loaded('session'),
    'openssl' => extension_loaded('openssl')
);

// Environment Check
$env_loaded = false;
if (file_exists(__DIR__ . '/../.env')) {
    $env_loaded = true;
}

// Database Connection Test
$db_connection = false;
$db_error = '';
$table_count = 0;

try {
    $pdo = getPDOConnection();
    $db_connection = true;
    
    // Count tables
    $stmt = $pdo->query("SHOW TABLES");
    $table_count = $stmt->rowCount();
    
} catch (Exception $e) {
    $db_error = $e->getMessage();
}

// Test Data
$test_products = array();
if ($db_connection) {
    try {
        // Try to fetch sample products
        $stmt = $pdo->query("SELECT ItemID, item_title FROM Items LIMIT 3");
        while ($row = $stmt->fetch()) {
            $test_products[] = array(
                'id' => $row['ItemID'],
                'name' => $row['item_title']
            );
        }
    } catch (Exception $e) {
        // Table might not exist or have different structure
    }
}

// Image URL Test
$base_url = getBaseUrl();
$sample_image_url = $base_url . '/pdf/1/sample-product.jpg';

// Response
$response = array(
    'success' => true,
    'message' => 'API is working with PHP 5.6!',
    'timestamp' => date('Y-m-d H:i:s'),
    'system' => array(
        'php_version' => $php_version,
        'php_5_6_compatible' => $php_check,
        'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
        'api_version' => '1.0.0'
    ),
    'extensions' => $extensions,
    'environment' => array(
        'env_file_exists' => $env_loaded,
        'base_url' => $base_url
    ),
    'database' => array(
        'connected' => $db_connection,
        'error' => $db_error,
        'tables_found' => $table_count
    ),
    'sample_data' => array(
        'products' => $test_products,
        'image_url_format' => $sample_image_url
    ),
    'endpoints' => array(
        'auth' => $base_url . '/API/v1/auth/validate.php',
        'products' => $base_url . '/API/v1/products/list.php',
        'product_detail' => $base_url . '/API/v1/products/detail.php?id={product_id}',
        'cart' => $base_url . '/API/v1/cart/get.php',
        'categories' => $base_url . '/API/v1/categories/list.php'
    ),
    'instructions' => array(
        'next_steps' => array(
            'Configure .env file with database credentials',
            'Test database connection',
            'Implement SSO validation',
            'Test each endpoint'
        )
    )
);

// Output JSON
echo json_encode($response, JSON_PRETTY_PRINT);
?>