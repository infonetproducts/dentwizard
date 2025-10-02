<?php
// Database configuration file
// Update these values with your actual database credentials

define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_database_name');

// Create database connection
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die(json_encode([
            'success' => false,
            'error' => 'Database connection failed'
        ]));
    }
    
    mysqli_set_charset($conn, "utf8");
    return $conn;
}

// Get CID (Client ID) - adjust based on your system
function getCID() {
    // You may need to get this from session or request
    // For now, using a default or from GET parameter
    if (isset($_GET['cid'])) {
        return intval($_GET['cid']);
    }
    // Default CID - update this based on your needs
    return 56; // Change this to your actual CID
}
?>
