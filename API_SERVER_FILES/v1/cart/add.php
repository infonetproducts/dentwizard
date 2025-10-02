<?php
// CORS Headers - MUST BE FIRST
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once '../db_config.php';

// Set content type
header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Product ID and quantity are required'
    ]);
    exit;
}

$product_id = intval($input['product_id']);
$quantity = intval($input['quantity']);

if ($quantity <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Quantity must be greater than 0'
    ]);
    exit;
}

// Get database connection
$conn = getDBConnection();
$CID = getCID();

// Get product details from database with proper column names
$sql = "SELECT 
    ID,
    item_title,
    Price,
    ImageFile,
    FormID,
    CID
FROM Items 
WHERE ID = $product_id 
AND CID = $CID 
AND status_item = 'Y'";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Product not found'
    ]);
    exit;
}

$product = mysqli_fetch_assoc($result);

// Build image URL
$image_url = '';
if (!empty($product['ImageFile'])) {
    $image_url = "https://dentwizard.lgstore.com/pdf/{$product['CID']}/{$product['ImageFile']}";
}

// Start session for cart
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Create cart item with properly mapped fields
$cart_item = [
    'id' => intval($product['ID']),
    'name' => $product['item_title'] ?: 'Untitled Product',
    'price' => floatval($product['Price']),
    'quantity' => $quantity,
    'image_url' => $image_url,
    'sku' => $product['FormID'] ?: ''
];

// Check if item already in cart
$found = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $product_id) {
        // Update quantity
        $_SESSION['cart'][$key]['quantity'] += $quantity;
        $found = true;
        break;
    }
}

// Add new item if not found
if (!$found) {
    $_SESSION['cart'][] = $cart_item;
}

// Close connection
mysqli_close($conn);

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => count($_SESSION['cart'])
], JSON_PRETTY_PRINT);
?>
