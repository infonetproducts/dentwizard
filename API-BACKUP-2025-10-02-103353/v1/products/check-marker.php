<?php
header("Content-Type: application/json");
$content = file_get_contents('detail.php');
$has_marker = (strpos($content, 'TEST MARKER: 2025-10-01-20:30') !== false);
echo json_encode(array(
    'marker_found' => $has_marker,
    'message' => $has_marker ? 'Server is using the uploaded file' : 'Server NOT using uploaded file - check path or clear cache'
));
?>
