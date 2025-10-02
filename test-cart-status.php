<?php
// Test cart clearing directly in the database
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id');

require_once('../config/db_connect.php');

// Test getting Jamie's cart status
$user_id = 20296; // Jamie's ID

// Check what cart-related tables exist
$tables_query = "SHOW TABLES LIKE '%cart%'";
$tables_result = $conn->query($tables_query);
$cart_tables = [];
while ($row = $tables_result->fetch_array()) {
    $cart_tables[] = $row[0];
}

// Check if there's a Cart or cart_items table
$response = [
    'cart_tables_found' => $cart_tables,
    'session_based' => empty($cart_tables),
];

// If cart_items table exists, check its contents
if (in_array('cart_items', $cart_tables) || in_array('Cart_items', $cart_tables) || in_array('CartItems', $cart_tables)) {
    $table_name = in_array('cart_items', $cart_tables) ? 'cart_items' : 
                  (in_array('Cart_items', $cart_tables) ? 'Cart_items' : 'CartItems');
    
    $cart_query = "SELECT COUNT(*) as count FROM $table_name WHERE user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_data = $result->fetch_assoc();
    
    $response['jamie_cart_items'] = $cart_data['count'];
    $response['cart_table_used'] = $table_name;
}

// Check if cart is stored in session or temp storage
session_start();
if (isset($_SESSION['cart'])) {
    $response['session_cart'] = $_SESSION['cart'];
}

echo json_encode($response);
?>
