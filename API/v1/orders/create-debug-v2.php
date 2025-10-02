<?php
// DEBUG VERSION - Shows exactly what's being received and saved
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die(json_encode(array('success' => false, 'error' => 'Database connection failed')));
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

// Debug array to track everything
$debug = array(
    'raw_input' => file_get_contents('php://input'),
    'decoded_input' => $input,
    'items_array' => isset($input['items']) ? $input['items'] : null,
    'first_item' => isset($input['items'][0]) ? $input['items'][0] : null
);

// Check if we have items with attributes
if (isset($input['items']) && is_array($input['items'])) {
    foreach ($input['items'] as $index => $item) {
        $debug['item_' . $index] = array(
            'product_id' => isset($item['product_id']) ? $item['product_id'] : 'NOT SET',
            'size' => isset($item['size']) ? $item['size'] : 'NOT SET',
            'color' => isset($item['color']) ? $item['color'] : 'NOT SET',
            'artwork' => isset($item['artwork']) ? $item['artwork'] : 'NOT SET'
        );
    }
}

// Return debug info
echo json_encode(array(
    'success' => true,
    'message' => 'Debug info - check what attributes are being received',
    'debug_info' => $debug
));

$mysqli->close();
?>