<?php
// PHP 5.6 COMPATIBLE TEST - Upload as test-php56.php
// No PHP 7+ features

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Use array() syntax for PHP 5.6
$response = array(
    'php_version' => phpversion(),
    'php_5_6_compatible' => version_compare(PHP_VERSION, '5.6.0', '>='),
    'status' => 'working'
);

echo json_encode($response);
?>