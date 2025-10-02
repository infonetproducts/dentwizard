<?php
// Script to get descriptions for FormIDs from database
header('Content-Type: application/json');

// Database connection
$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get FormID from query parameter
$form_id = isset($_GET['form_id']) ? trim($_GET['form_id']) : '';

if (empty($form_id)) {
    die(json_encode(['error' => 'No FormID provided']));
}

// Query to find item by FormID (checking multiple possible columns)
$sql = "SELECT 
    ID,
    ItemNumber,
    Name,
    Description,
    FormID
FROM Items 
WHERE FormID = ? 
   OR ItemNumber = ?
   OR ID = ?
LIMIT 1";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sss", $form_id, $form_id, $form_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $item = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'data' => [
            'form_id' => $form_id,
            'name' => $item['Name'],
            'description' => $item['Description'] ?: $item['Name']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'data' => [
            'form_id' => $form_id,
            'description' => ''
        ]
    ]);
}

$stmt->close();
$mysqli->close();
?>