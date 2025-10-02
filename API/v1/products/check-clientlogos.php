<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

// Test with the CORRECT table name from detail.php
$logo_ids = "507,508,509";
$logo_query = "SELECT ID, Name, image_name, CID 
               FROM ClientLogos 
               WHERE ID IN ($logo_ids) 
               ORDER BY Name";

$result = $mysqli->query($logo_query);

if ($result === false) {
    echo json_encode([
        'error' => 'Query failed',
        'sql_error' => $mysqli->error
    ], JSON_PRETTY_PRINT);
} else {
    $logos = [];
    while ($row = $result->fetch_assoc()) {
        $logos[] = $row;
    }
    
    echo json_encode([
        'logos_requested' => [507, 508, 509],
        'logos_found' => count($logos),
        'missing_count' => 3 - count($logos),
        'logos' => $logos,
        'conclusion' => count($logos) == 0 ? 'NO LOGOS CONFIGURED - Product references non-existent logo IDs' : (count($logos) < 3 ? 'PARTIAL - Some logos missing' : 'ALL LOGOS EXIST')
    ], JSON_PRETTY_PRINT);
}

$mysqli->close();
?>
