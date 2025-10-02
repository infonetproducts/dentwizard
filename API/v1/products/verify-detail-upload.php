<?php
// This line should exist around line 94 in detail.php
// It should have @ symbol for error suppression
header("Content-Type: application/json");
$content = file_get_contents('detail.php');
$line_to_check = '$size_result = @$mysqli->query($size_sql);';
$has_fix = (strpos($content, $line_to_check) !== false);
echo json_encode(array(
    'file_exists' => file_exists('detail.php'),
    'has_error_suppression_fix' => $has_fix,
    'file_size' => filesize('detail.php'),
    'last_modified' => date('Y-m-d H:i:s', filemtime('detail.php'))
));
?>
