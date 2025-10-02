<?php
// Analyze the password encoding method
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Test different encoding methods
$test_password = "password";
$stored_hash = "jSNLfTCRksoe";

$results = [
    'stored_hash' => $stored_hash,
    'original_password' => $test_password,
    'tests' => []
];

// Test 1: Base64 encoding
$results['tests']['base64'] = [
    'encoded' => base64_encode($test_password),
    'matches' => (base64_encode($test_password) === $stored_hash)
];

// Test 2: MD5
$results['tests']['md5'] = [
    'hash' => md5($test_password),
    'substr12' => substr(md5($test_password), 0, 12),
    'matches' => (substr(md5($test_password), 0, 12) === $stored_hash)
];

// Test 3: Simple ROT13
$results['tests']['rot13'] = [
    'encoded' => str_rot13($test_password),
    'matches' => (str_rot13($test_password) === $stored_hash)
];

// Test 4: Custom simple encoding (shift characters)
function simpleEncode($str) {
    $result = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $result .= chr(ord($str[$i]) + 3);
    }
    return $result;
}
$results['tests']['shift3'] = [
    'encoded' => simpleEncode($test_password),
    'matches' => (simpleEncode($test_password) === $stored_hash)
];

// Test 5: Check if it's reversible
$results['tests']['base64_decode'] = [
    'decoded' => base64_decode($stored_hash),
    'readable' => preg_match('/^[\x20-\x7E]+$/', base64_decode($stored_hash))
];

// Test 6: Check Joe Lorenzo's password for pattern
$sql = "SELECT Password FROM Users WHERE ID = 19346";
$result = $mysqli->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $results['joe_password'] = $row['Password'];
    $results['joe_password_length'] = strlen($row['Password']);
}

// Test 7: Try to decode as simple substitution
$decoded = '';
for ($i = 0; $i < strlen($stored_hash); $i++) {
    $char = $stored_hash[$i];
    if (ctype_alpha($char)) {
        $offset = ctype_upper($char) ? 65 : 97;
        $decoded .= chr((ord($char) - $offset - 3 + 26) % 26 + $offset);
    } else {
        $decoded .= $char;
    }
}
$results['tests']['caesar_decode'] = [
    'decoded' => $decoded,
    'matches' => ($decoded === $test_password)
];

echo json_encode($results, JSON_PRETTY_PRINT);

$mysqli->close();
?>