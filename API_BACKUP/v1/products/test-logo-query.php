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

// Test the exact logo query that detail.php would run
$logo_ids = "507,508,509";
$logo_query = "SELECT id, item_logo_type FROM item_logos WHERE id IN ($logo_ids)";

echo json_encode(['query' => $logo_query], JSON_PRETTY_PRINT) . "\n\n";

$result = $mysqli->query($logo_query);

if ($result === false) {
    echo json_encode([
        'error' => 'Logo query failed',
        'sql_error' => $mysqli->error,
        'query' => $logo_query
    ], JSON_PRETTY_PRINT);
} else {
    $logos = [];
    while ($row = $result->fetch_assoc()) {
        $logos[] = $row;
    }
    echo json_encode([
        'success' => true,
        'logos_found' => count($logos),
        'logos' => $logos
    ], JSON_PRETTY_PRINT);
}

$mysqli->close();
?>
