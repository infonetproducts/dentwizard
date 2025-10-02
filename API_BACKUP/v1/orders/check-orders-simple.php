<?php
// Simple check for Orders table structure - PHP 5.6 compatible
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Checking Orders Table Structure ===\n\n";

// Database connection
$servername = "localhost";
$username = "rwaf";
$password = "Py*uhb\$L\$##";
$dbname = "rwaf";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Database connected successfully\n\n";

// Check if Orders table exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'Orders'");
if (mysqli_num_rows($check_table) > 0) {
    echo "Orders table found\n\n";
} else {
    die("Orders table NOT found");
}

// Get Orders table columns
echo "=== Orders Table Columns ===\n";
$query = "SHOW COLUMNS FROM Orders";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error getting columns: " . mysqli_error($conn));
}

$important_fields = array();
while ($row = mysqli_fetch_assoc($result)) {
    $field = $row['Field'];
    $type = $row['Type'];
    
    echo sprintf("%-30s | %s\n", $field, $type);
    
    // Track important fields
    if (stripos($field, 'order') !== false || 
        stripos($field, 'user') !== false || 
        stripos($field, 'ship') !== false ||
        stripos($field, 'total') !== false ||
        stripos($field, 'payment') !== false) {
        $important_fields[] = $field;
    }
}

echo "\n=== Important Fields Found ===\n";
foreach ($important_fields as $field) {
    echo "- " . $field . "\n";
}

// Check a sample order
echo "\n=== Sample Order Data ===\n";
$sample_query = "SELECT * FROM Orders ORDER BY id DESC LIMIT 1";
$sample_result = mysqli_query($conn, $sample_query);

if ($sample_result && mysqli_num_rows($sample_result) > 0) {
    $sample = mysqli_fetch_assoc($sample_result);
    echo "Latest order ID: " . (isset($sample['id']) ? $sample['id'] : 'N/A') . "\n";
    echo "Order ID: " . (isset($sample['order_id']) ? $sample['order_id'] : 'N/A') . "\n";
    echo "User ID: " . (isset($sample['user_id']) ? $sample['user_id'] : 'N/A') . "\n";
    echo "Order Total: " . (isset($sample['order_total']) ? $sample['order_total'] : 'N/A') . "\n";
} else {
    echo "No orders found in table\n";
}

mysqli_close($conn);

echo "\n=== Check Complete ===";
?>