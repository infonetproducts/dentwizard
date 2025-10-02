<?php
// Check what password field exists in Users table
header("Content-Type: application/json");

$mysqli = @new mysqli('localhost', 'rwaf', 'Py*uhb$L$##', 'rwaf');

if ($mysqli->connect_error) {
    die(json_encode(array('error' => 'Database connection failed')));
}

// Check columns in Users table
$result = $mysqli->query("DESCRIBE Users");
$password_fields = array();
$all_fields = array();

while ($row = $result->fetch_assoc()) {
    $field = $row['Field'];
    $all_fields[] = $field;
    
    // Look for password-related fields
    if (stripos($field, 'pass') !== false || 
        stripos($field, 'pwd') !== false) {
        $password_fields[] = $field;
    }
}

// Check if Jamison exists and what data we have
$email = 'jkrugger@infonetproducts.com';
$sql = "SELECT * FROM Users WHERE Email = '$email' LIMIT 1";
$result = $mysqli->query($sql);
$user_data = null;

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_data = array(
        'found' => true,
        'id' => $user['ID'],
        'name' => $user['Name'],
        'email' => $user['Email']
    );
    
    // Check each potential password field
    foreach ($password_fields as $field) {
        if (isset($user[$field]) && !empty($user[$field])) {
            $user_data['has_' . strtolower($field)] = true;
            $user_data[$field . '_length'] = strlen($user[$field]);
        }
    }
}

echo json_encode(array(
    'password_fields_found' => $password_fields,
    'all_fields_count' => count($all_fields),
    'user_data' => $user_data,
    'test_password' => 'dentwizard'
));

$mysqli->close();
?>