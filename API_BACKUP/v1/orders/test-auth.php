<?php
// Test what data is being received
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

// Get raw input
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

// Debug what we're receiving
$debug = array(
    'method' => $_SERVER['REQUEST_METHOD'],
    'raw_input_length' => strlen($input_raw),
    'decoded_input' => $input,
    'auth_token_received' => isset($input['auth_token']) ? $input['auth_token'] : 'NOT FOUND',
    'user_id_received' => isset($input['user_id']) ? $input['user_id'] : 'NOT FOUND'
);

// If we have auth_token, try to decode it
if (isset($input['auth_token'])) {
    $token = $input['auth_token'];
    $decoded = base64_decode($token);
    $debug['token_decoded'] = $decoded;
    
    if ($decoded) {
        $parts = explode(':', $decoded);
        $debug['token_parts'] = $parts;
        $debug['token_valid'] = count($parts) === 3;
    }
}

die(json_encode($debug, JSON_PRETTY_PRINT));
?>