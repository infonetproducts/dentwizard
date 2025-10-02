<?php
// Get description by Item ID
header('Content-Type: application/json');

$mysqli = new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get Item ID from query parameter
$item_id = isset($_GET['item_id']) ? trim($_GET['item_id']) : '';

if (empty($item_id)) {
    die(json_encode(['error' => 'No Item ID provided']));
}

// Query to find item by ID
$sql = "SELECT 
    ID,
    Description,
    item_title
FROM Items 
WHERE ID = ?
LIMIT 1";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $item = $result->fetch_assoc();
    
    // Use Description if available, otherwise use item_title
    $description = !empty($item['Description']) ? $item['Description'] : $item['item_title'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'item_id' => $item_id,
            'description' => $description
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'data' => [
            'item_id' => $item_id,
            'description' => ''
        ]
    ]);
}

$stmt->close();
$mysqli->close();
?>