<?php
/**
 * Check User Endpoint - PHP 5.6 Compatible
 * 
 * Determines if user exists and what authentication type they should use
 * 
 * @endpoint GET /api/check-user.php?email=user@example.com
 * @return JSON
 */

// CORS Headers (same as your existing endpoint files)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load database configuration (adjust path to match your setup)
require_once dirname(__FILE__) . '/config/saml-config.php';

/**
 * Get email from request
 */
function getEmailFromRequest() {
    $email = '';
    
    // Try GET parameter first
    if (isset($_GET['email'])) {
        $email = $_GET['email'];
    }
    // Try POST parameter
    elseif (isset($_POST['email'])) {
        $email = $_POST['email'];
    }
    // Try JSON body
    else {
        $json = file_get_contents('php://input');
        if ($json) {
            $data = json_decode($json, true);
            if (isset($data['email'])) {
                $email = $data['email'];
            }
        }
    }
    
    return trim($email);
}

/**
 * Check if email domain requires SSO
 */
function requiresSSO($email) {
    $ssoDomains = array('dentwizard.com'); // Add more domains as needed
    
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }
    
    $domain = strtolower($parts[1]);
    return in_array($domain, $ssoDomains);
}

/**
 * Send JSON response
 */
function sendResponse($data) {
    echo json_encode($data);
    exit();
}

// Main execution
try {
    // Get email from request
    $email = getEmailFromRequest();
    
    // Validate email
    if (empty($email)) {
        sendResponse(array(
            'success' => false,
            'exists' => false,
            'message' => 'Email parameter is required'
        ));
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(array(
            'success' => false,
            'exists' => false,
            'message' => 'Invalid email format'
        ));
    }
    
    // Check if domain requires SSO
    $needsSSO = requiresSSO($email);
    
    // Get database config
    $config = getSAMLConfig();
    $dbConfig = $config['database'];
    
    // Connect to database (PHP 5.6 compatible mysqli)
    $conn = mysqli_connect(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );
    
    if (mysqli_connect_errno()) {
        error_log('Database connection error: ' . mysqli_connect_error());
        sendResponse(array(
            'success' => false,
            'exists' => false,
            'message' => 'Database connection error'
        ));
    }
    
    // Query user
    $email_escaped = mysqli_real_escape_string($conn, $email);
    $query = "SELECT user_id, email, auth_type, is_active FROM users WHERE email = '$email_escaped' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log('Database query error: ' . mysqli_error($conn));
        mysqli_close($conn);
        sendResponse(array(
            'success' => false,
            'exists' => false,
            'message' => 'Database query error'
        ));
    }
    
    $user = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    
    // User exists
    if ($user) {
        // Check if user is active
        if ($user['is_active'] != 1) {
            sendResponse(array(
                'success' => false,
                'exists' => true,
                'message' => 'Account is deactivated. Please contact your administrator.'
            ));
        }
        
        // Return user info
        sendResponse(array(
            'success' => true,
            'exists' => true,
            'auth_type' => $user['auth_type'],
            'requiresSSO' => $needsSSO,
            'message' => 'User found'
        ));
    }
    // User does not exist
    else {
        sendResponse(array(
            'success' => false,
            'exists' => false,
            'requiresSSO' => $needsSSO,
            'message' => 'User not found. Please contact your administrator to create your account.'
        ));
    }
    
} catch (Exception $e) {
    error_log('Error in check-user.php: ' . $e->getMessage());
    sendResponse(array(
        'success' => false,
        'exists' => false,
        'message' => 'An error occurred while checking user'
    ));
}
?>
