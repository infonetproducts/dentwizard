<?php
// TEST FILE - Upload this EXACTLY as shown
// NO spaces or blank lines before this <?php tag!
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    http_response_code(200);
    exit(); 
}
header('Content-Type: application/json');
echo json_encode([
    "cors_status" => "WORKING",
    "message" => "If you see this in the browser, CORS is fixed!",
    "timestamp" => date('Y-m-d H:i:s')
]);
?>
