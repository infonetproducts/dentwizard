<?php
// config/database.php
// PHP 5.6 Compatible Database Connection

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Database configuration
define('DB_HOST', getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost');
define('DB_NAME', getenv('DB_NAME') ? getenv('DB_NAME') : 'your_database');
define('DB_USER', getenv('DB_USER') ? getenv('DB_USER') : 'your_username');
define('DB_PASS', getenv('DB_PASS') ? getenv('DB_PASS') : 'your_password');

/**
 * Get PDO connection (PHP 5.6 compatible)
 * @return PDO
 */
function getPDOConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            );
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error (don't expose in production)
            error_log("Database connection error: " . $e->getMessage());
            
            // Return error response
            http_response_code(500);
            echo json_encode(array(
                'success' => false,
                'error' => 'Database connection error'
            ));
            exit;
        }
    }
    
    return $pdo;
}

/**
 * Get MySQL connection (legacy support)
 * @return resource
 */
function getMySQLConnection() {
    static $connection = null;
    
    if ($connection === null) {
        $connection = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        
        if (!$connection) {
            error_log("MySQL connection error: " . mysql_error());
            http_response_code(500);
            echo json_encode(array(
                'success' => false,
                'error' => 'Database connection error'
            ));
            exit;
        }
        
        mysql_select_db(DB_NAME, $connection);
        mysql_set_charset('utf8', $connection);
    }
    
    return $connection;
}

/**
 * Get base URL for images and assets
 * @return string
 */
function getBaseUrl() {
    $base_url = getenv('BASE_URL');
    if (!$base_url) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $base_url = $protocol . '://' . $host;
    }
    return $base_url;
}

/**
 * Safe array access for PHP 5.6
 * @param array $array
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function array_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}
?>