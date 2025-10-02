<?php
// Test what's breaking in add.php - Step by step

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Test 1: Can we output JSON?
if (isset($_GET['test']) && $_GET['test'] == '1') {
    echo json_encode(array('test' => 1, 'message' => 'Basic JSON works'));
    exit();
}

// Test 2: Can we start session?
if (isset($_GET['test']) && $_GET['test'] == '2') {
    session_start();
    echo json_encode(array('test' => 2, 'session_id' => session_id()));
    exit();
}

// Test 3: Can we connect to database?
if (isset($_GET['test']) && $_GET['test'] == '3') {
    $mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
    if ($mysqli->connect_error) {
        echo json_encode(array('test' => 3, 'error' => 'DB connection failed'));
    } else {
        echo json_encode(array('test' => 3, 'message' => 'DB connected OK'));
        $mysqli->close();
    }
    exit();
}

// Test 4: Can we query the database?
if (isset($_GET['test']) && $_GET['test'] == '4') {
    $mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');
    $query = "SELECT ID, item_title, Price FROM Items WHERE ID = 91754 LIMIT 1";
    $result = @$mysqli->query($query);
    
    if (!$result) {
        echo json_encode(array('test' => 4, 'error' => 'Query failed', 'mysql_error' => $mysqli->error));
    } else {
        $row = $result->fetch_assoc();
        echo json_encode(array('test' => 4, 'message' => 'Query OK', 'product' => $row));
    }
    $mysqli->close();
    exit();
}

// Test 5: Can we read POST data?
if (isset($_GET['test']) && $_GET['test'] == '5') {
    $input = json_decode(file_get_contents('php://input'), true);
    echo json_encode(array('test' => 5, 'input_received' => $input));
    exit();
}

// If no test specified
echo json_encode(array(
    'message' => 'Debug tests available',
    'tests' => array(
        '?test=1' => 'Basic JSON output',
        '?test=2' => 'Session start',
        '?test=3' => 'Database connection',
        '?test=4' => 'Database query',
        '?test=5' => 'POST data reading'
    )
));
?>