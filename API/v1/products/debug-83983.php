<?php
// Force error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Debug script for product 83983 following API structure
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

// Output something immediately to test if PHP is working
echo '{"debug":"script_started",';

// Database connection (same as detail.php)
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

echo '"db_info":"connecting",';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    echo '"connection":"failed","error":"' . $mysqli->connect_error . '"}';
    exit;
}

echo '"connection":"success",';

$product_id = 83983;

echo '"query_running":true,';

// Get the main product details (same query as detail.php)
$sql = "SELECT ID, item_title, Price, ImageFile, FormID, Description, item_logo_ids, product_tax_code 
        FROM Items 
        WHERE ID = $product_id 
        AND CID = 244 
        AND status_item = 'Y' 
        LIMIT 1";

$result = $mysqli->query($sql);

if (!$result) {
    echo '"query":"failed","error":"' . addslashes($mysqli->error) . '"}';
    exit;
}

echo '"query":"success",';

$product = $result->fetch_assoc();

if (!$product) {
    echo '"product":"not_found"}';
    exit;
}

echo '"product":"found","item_logo_ids":"' . addslashes($product['item_logo_ids'] ?? '') . '"}';
$mysqli->close();
?>
