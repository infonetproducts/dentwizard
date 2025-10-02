<?php
// User Addresses API - PHP 5.3 Compatible
// Place at: /lg/API/v1/user/addresses.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Database connection - same as your other APIs
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// Get user ID - same pattern as your other APIs
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 19346;

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Check if table exists first
    $table_check = $mysqli->query("SHOW TABLES LIKE 'user_addresses'");
    
    if ($table_check->num_rows == 0) {
        // Table doesn't exist - return empty array
        echo json_encode(array(
            'status' => 'success',
            'data' => array()
        ));
        exit();
    }
    
    // Get addresses
    $sql = "SELECT * FROM user_addresses WHERE user_id = $user_id ORDER BY is_default DESC, id DESC";
    $result = $mysqli->query($sql);
    
    $addresses = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row;
        }
    }
    
    echo json_encode(array(
        'status' => 'success',
        'data' => $addresses
    ));
}

if ($method == 'POST') {
    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if table exists, create if not
    $create_table = "CREATE TABLE IF NOT EXISTS `user_addresses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `nickname` varchar(100) DEFAULT NULL,
        `first_name` varchar(100) DEFAULT NULL,
        `last_name` varchar(100) DEFAULT NULL,
        `address1` varchar(255) NOT NULL,
        `address2` varchar(255) DEFAULT NULL,
        `city` varchar(100) NOT NULL,
        `state` varchar(50) NOT NULL,
        `zip` varchar(20) NOT NULL,
        `country` varchar(100) DEFAULT 'United States',
        `phone` varchar(20) DEFAULT NULL,
        `is_default` tinyint(1) DEFAULT 0,
        `created_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    
    $mysqli->query($create_table);
    
    // Prepare data (with defaults for missing fields)
    $nickname = isset($input['nickname']) ? $mysqli->real_escape_string($input['nickname']) : '';
    $first_name = isset($input['first_name']) ? $mysqli->real_escape_string($input['first_name']) : '';
    $last_name = isset($input['last_name']) ? $mysqli->real_escape_string($input['last_name']) : '';
    $address1 = isset($input['address1']) ? $mysqli->real_escape_string($input['address1']) : '';
    $address2 = isset($input['address2']) ? $mysqli->real_escape_string($input['address2']) : '';
    $city = isset($input['city']) ? $mysqli->real_escape_string($input['city']) : '';
    $state = isset($input['state']) ? $mysqli->real_escape_string($input['state']) : '';
    $zip = isset($input['zip']) ? $mysqli->real_escape_string($input['zip']) : '';
    $country = isset($input['country']) ? $mysqli->real_escape_string($input['country']) : 'United States';
    $phone = isset($input['phone']) ? $mysqli->real_escape_string($input['phone']) : '';
    $is_default = isset($input['is_default']) && $input['is_default'] ? 1 : 0;
    
    // If this is the default, unset other defaults
    if ($is_default) {
        $mysqli->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id");
    }
    
    // Insert address
    $sql = "INSERT INTO user_addresses (
        user_id, nickname, first_name, last_name, 
        address1, address2, city, state, zip, 
        country, phone, is_default, created_at
    ) VALUES (
        $user_id, '$nickname', '$first_name', '$last_name',
        '$address1', '$address2', '$city', '$state', '$zip',
        '$country', '$phone', $is_default, NOW()
    )";
    
    if ($mysqli->query($sql)) {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Address saved successfully',
            'id' => $mysqli->insert_id
        ));
    } else {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Failed to save address'
        ));
    }
}

$mysqli->close();
?>