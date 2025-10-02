<?php
// API/v1/auth/check-type.php
// Checks what authentication method a user should use
// FIXED to match working API structure

require_once '../../cors.php';
header("Content-Type: application/json");

// Database connection - matching working APIs structure
$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$email = isset($data['email']) ? $mysqli->real_escape_string($data['email']) : '';

if (empty($email)) {
    die(json_encode(array('error' => 'Email required')));
}

// Check if user exists and their auth type
$sql = "SELECT 
    ID as user_id,
    Email as email,
    Name as name
    FROM Users 
    WHERE Email = '$email' 
    LIMIT 1";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Determine auth type based on email domain
    $auth_type = 'standard'; // Default to standard for now
    if (strpos($user['email'], '@dentwizard.com') !== false) {
        // For now, allow standard login for @dentwizard.com
        // Later change this to 'sso' to force SSO
        $auth_type = 'standard'; 
    }
    
    echo json_encode(array(
        'exists' => true,
        'auth_type' => $auth_type,
        'email' => $user['email']
    ));
} else {
    // User doesn't exist
    echo json_encode(array(
        'exists' => false,
        'auth_type' => 'standard',
        'message' => 'User not found'
    ));
}

$mysqli->close();
?>