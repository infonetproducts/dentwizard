<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

session_start();

// Test data
$test_results = [];

// Check session
$test_results['session'] = [
    'session_id' => session_id(),
    'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET',
    'user_email' => isset($_SESSION['userEmail']) ? $_SESSION['userEmail'] : 'NOT SET',
    'all_session_data' => $_SESSION
];

// Test database connection
$test_results['database'] = [];

// Try connection method 1
try {
    $mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
    if ($mysqli->connect_error) {
        $test_results['database']['method1_mysqli'] = 'Failed: ' . $mysqli->connect_error;
    } else {
        $test_results['database']['method1_mysqli'] = 'SUCCESS';
        
        // Test query
        $result = $mysqli->query("SELECT ID, Email, Name FROM Users WHERE ID = " . intval($_SESSION['user_id']));
        if ($result) {
            $user = $result->fetch_assoc();
            $test_results['database']['user_found'] = $user;
        } else {
            $test_results['database']['query_error'] = $mysqli->error;
        }
        $mysqli->close();
    }
} catch (Exception $e) {
    $test_results['database']['method1_mysqli'] = 'Exception: ' . $e->getMessage();
}

// Try connection method 2 (using @ to suppress warnings)
$mysqli2 = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
if ($mysqli2 && !$mysqli2->connect_error) {
    $test_results['database']['method2_suppressed'] = 'SUCCESS';
    $mysqli2->close();
} else {
    $test_results['database']['method2_suppressed'] = 'Failed';
}

// Check PHP extensions
$test_results['php'] = [
    'mysqli_extension' => extension_loaded('mysqli'),
    'php_version' => phpversion()
];

echo json_encode($test_results, JSON_PRETTY_PRINT);
?>
