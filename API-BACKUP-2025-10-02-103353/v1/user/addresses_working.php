<?php
// Addresses API - Matches your working PHP files exactly
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Exit for OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Your exact connection pattern
$host = 'localhost';
$user = 'rwaf';  
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = new mysqli($host, $user, $pass, $db);

// Simple check
if ($mysqli->connect_errno) {
    die('{"status":"error","message":"Connection failed"}');
}

// Default user
$user_id = 19346;

// For GET request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Just return empty for now - no table needed
    echo '{"status":"success","data":[]}';
}

// For POST request  
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo '{"status":"success","message":"Address feature coming soon"}';
}

$mysqli->close();
?>