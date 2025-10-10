<?php
/**
 * Check User Authentication Type
 * 
 * Determines whether a user should use standard login or SSO
 * 
 * Endpoint: /lg/API/v1/auth/check-user.php
 * Method: POST
 * Body: { "email": "user@example.com" }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';

// Get request body
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';

if (empty($email)) {
    echo json_encode([
        'exists' => false,
        'auth_type' => 'standard',
        'message' => 'Email is required'
    ]);
    exit;
}

// Check if email is from SSO domain
$sso_domains = ['dentwizard.com'];
$email_domain = substr(strrchr($email, "@"), 1);
$requires_sso = in_array($email_domain, $sso_domains);

// Check if user exists in database
try {
    $stmt = $pdo->prepare("SELECT id, email, auth_type FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // User exists
        $auth_type = $requires_sso ? 'sso' : ($user['auth_type'] ?? 'standard');
        
        echo json_encode([
            'exists' => true,
            'auth_type' => $auth_type,
            'requires_sso' => $requires_sso,
            'message' => 'User found'
        ]);
    } else {
        // User doesn't exist - indicate required auth type
        echo json_encode([
            'exists' => false,
            'auth_type' => $requires_sso ? 'sso' : 'standard',
            'requires_sso' => $requires_sso,
            'message' => 'User not found'
        ]);
    }
} catch (PDOException $e) {
    error_log('Database error in check-user: ' . $e->getMessage());
    echo json_encode([
        'exists' => false,
        'auth_type' => 'standard',
        'message' => 'Error checking user'
    ]);
}
?>
