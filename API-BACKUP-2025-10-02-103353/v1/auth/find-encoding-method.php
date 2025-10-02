<?php
// Test which encoding method matches your system
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Test password encoding methods
$password = "password";
$stored_in_db = "j5RLfTtRxso=";

$results = [
    'password' => $password,
    'stored_in_db' => $stored_in_db,
    'encoding_tests' => []
];

// Test 1: MD5 hash, first 8 bytes, base64
$md5_full = md5($password, true);
$md5_8bytes = substr($md5_full, 0, 8);
$md5_encoded = base64_encode($md5_8bytes);
$results['encoding_tests']['md5_8bytes'] = [
    'result' => $md5_encoded,
    'matches' => ($md5_encoded === $stored_in_db) ? '✓ MATCH!' : 'No match'
];

// Test 2: SHA1 hash, first 8 bytes, base64
$sha1_full = sha1($password, true);
$sha1_8bytes = substr($sha1_full, 0, 8);
$sha1_encoded = base64_encode($sha1_8bytes);
$results['encoding_tests']['sha1_8bytes'] = [
    'result' => $sha1_encoded,
    'matches' => ($sha1_encoded === $stored_in_db) ? '✓ MATCH!' : 'No match'
];

// Test 3: SHA256 hash, first 8 bytes, base64
$sha256_full = hash('sha256', $password, true);
$sha256_8bytes = substr($sha256_full, 0, 8);
$sha256_encoded = base64_encode($sha256_8bytes);
$results['encoding_tests']['sha256_8bytes'] = [
    'result' => $sha256_encoded,
    'matches' => ($sha256_encoded === $stored_in_db) ? '✓ MATCH!' : 'No match'
];

// Test 4: DES crypt (common in older PHP systems)
if (function_exists('crypt')) {
    // DES uses first 2 chars as salt
    $des_result = crypt($password, 'j5');
    $results['encoding_tests']['des_crypt'] = [
        'result' => $des_result,
        'matches' => ($des_result === $stored_in_db) ? '✓ MATCH!' : 'No match'
    ];
}

// Test 5: Custom XOR encoding (some legacy systems use this)
function xorEncode($str, $key = 'dentwizard') {
    $result = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $result .= chr(ord($str[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode(substr($result, 0, 8));
}
$xor_encoded = xorEncode($password);
$results['encoding_tests']['xor_custom'] = [
    'result' => $xor_encoded,
    'matches' => ($xor_encoded === $stored_in_db) ? '✓ MATCH!' : 'No match'
];

// Show which method matches
foreach ($results['encoding_tests'] as $method => $test) {
    if (strpos($test['matches'], 'MATCH') !== false) {
        $results['FOUND_METHOD'] = $method;
        break;
    }
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>