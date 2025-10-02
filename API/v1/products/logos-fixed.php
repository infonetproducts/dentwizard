<?php
// API endpoint to get available logos for a product
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// Get product ID from query parameter - matching detail.php format
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    die(json_encode(array('status' => 'error', 'message' => 'Invalid product ID')));
}

// First get the item_logo_ids for this product with CID check
$query = "SELECT item_logo_ids, CID FROM Items WHERE ID = $product_id AND CID = 244 LIMIT 1";
$result = $mysqli->query($query);

if (!$result) {
    die(json_encode(array('status' => 'error', 'message' => 'Query failed')));
}

$product = $result->fetch_assoc();

if (!$product) {
    die(json_encode(array('status' => 'error', 'message' => 'Product not found')));
}

$item_logo_ids = $product['item_logo_ids'];
$product_cid = $product['CID'];

// Default logos array
$logos = array();

if (!empty($item_logo_ids)) {
    // Get the logos from ClientLogos table
    $query = "SELECT ID, Name, image_name, CID FROM ClientLogos WHERE ID IN ($item_logo_ids) ORDER BY Name";
    $result = $mysqli->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logo_image = '';
            if (!empty($row['image_name'])) {
                $logo_image = 'https://dentwizard.lgstore.com/pdf/' . $row['CID'] . '/' . $row['image_name'];
            }
            
            $logos[] = array(
                'id' => $row['ID'],
                'name' => $row['Name'],
                'image' => $logo_image
            );
        }
    }
}

$mysqli->close();

// Return response - matching detail.php structure
$response = array(
    'status' => 'success',
    'data' => array(
        'product_id' => $product_id,
        'logos' => $logos,
        'has_logos' => !empty($logos)
    )
);

echo json_encode($response);
?>