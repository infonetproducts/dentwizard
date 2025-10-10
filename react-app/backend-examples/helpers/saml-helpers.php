<?php
/**
 * SAML Helper Functions - PHP 5.6 Compatible
 * 
 * Utility functions for SAML authentication
 * All functions are PHP 5.6 compatible
 */

/**
 * Get user by email from database
 * 
 * @param string $email User email address
 * @return array|null User data or null if not found
 */
function getUserByEmail($email) {
    try {
        $conn = getDatabaseConnection();
        $email_escaped = mysqli_real_escape_string($conn, $email);
        
        $query = "SELECT * FROM users WHERE email = '$email_escaped' LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            error_log('getUserByEmail query error: ' . mysqli_error($conn));
            mysqli_close($conn);
            return null;
        }
        
        $user = mysqli_fetch_assoc($result);
        mysqli_close($conn);
        
        return $user ? $user : null;
        
    } catch (Exception $e) {
        error_log('getUserByEmail error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Update user's last login timestamp
 * 
 * @param int $userId User ID
 * @return bool Success status
 */
function updateLastLogin($userId) {
    try {
        $conn = getDatabaseConnection();
        $userId = intval($userId);
        
        $query = "UPDATE users SET last_login = NOW() WHERE user_id = $userId";
        $result = mysqli_query($conn, $query);
        
        mysqli_close($conn);
        return $result !== false;
        
    } catch (Exception $e) {
        error_log('updateLastLogin error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Generate JWT token for user
 * 
 * @param array $user User data
 * @return string JWT token
 */
function generateJWT($user) {
    // Simple JWT generation (you may want to use a library in production)
    $header = array(
        'typ' => 'JWT',
        'alg' => 'HS256'
    );
    
    $payload = array(
        'user_id' => $user['user_id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'budget' => $user['budget'],
        'auth_type' => $user['auth_type'],
        'iat' => time(),
        'exp' => time() + (8 * 60 * 60) // 8 hours
    );
    
    $base64UrlHeader = base64url_encode(json_encode($header));
    $base64UrlPayload = base64url_encode(json_encode($payload));
    
    // Use a secret key (should be in environment variable in production)
    $secret = 'your_jwt_secret_key_here'; // ← UPDATE THIS in production
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64url_encode($signature);
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

/**
 * Base64 URL encode
 * 
 * @param string $data Data to encode
 * @return string Encoded data
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Log SAML authentication attempt
 * 
 * @param string $email User email
 * @param string $status Status (success/failure)
 * @param string $message Optional message
 * @return void
 */
function logSAMLAttempt($email, $status, $message = '') {
    $logMessage = sprintf(
        "[%s] SAML Login: %s - %s - %s",
        date('Y-m-d H:i:s'),
        $email,
        $status,
        $message
    );
    error_log($logMessage);
}

/**
 * Redirect to React app with success
 * 
 * @param string $token JWT token
 * @param array $user User data
 * @return void
 */
function redirectToReactSuccess($token, $user) {
    // Build React callback URL
    $frontendUrl = 'https://dentwizardapparel.com'; // ← UPDATE if needed
    
    // Encode user data
    $userData = base64_encode(json_encode(array(
        'user_id' => $user['user_id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'budget' => $user['budget']
    )));
    
    // Build redirect URL
    $redirectUrl = $frontendUrl . '/auth/sso-callback?token=' . urlencode($token) . '&user=' . urlencode($userData);
    
    header('Location: ' . $redirectUrl);
    exit();
}

/**
 * Redirect to React app with error
 * 
 * @param string $error Error message
 * @return void
 */
function redirectToReactError($error) {
    $frontendUrl = 'https://dentwizardapparel.com'; // ← UPDATE if needed
    $redirectUrl = $frontendUrl . '/auth/sso-callback?error=' . urlencode($error);
    
    header('Location: ' . $redirectUrl);
    exit();
}

/**
 * Send JSON response
 * 
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 * @return void
 */
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

/**
 * Get SSO domains
 * 
 * @return array List of SSO-enabled domains
 */
function getSSODomains() {
    return array('dentwizard.com'); // Add more domains as needed
}

/**
 * Check if email requires SSO
 * 
 * @param string $email Email address
 * @return bool True if email requires SSO
 */
function requiresSSO($email) {
    $ssoDomains = getSSODomains();
    
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }
    
    $domain = strtolower($parts[1]);
    return in_array($domain, $ssoDomains);
}

/**
 * Validate SAML response attributes
 * 
 * @param array $attributes SAML attributes
 * @return array Validation result array(success, message, email)
 */
function validateSAMLAttributes($attributes) {
    // Check for email (NameID or email attribute)
    $email = null;
    if (isset($attributes['email']) && is_array($attributes['email']) && count($attributes['email']) > 0) {
        $email = $attributes['email'][0];
    } elseif (isset($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'])) {
        $emails = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'];
        if (is_array($emails) && count($emails) > 0) {
            $email = $emails[0];
        }
    }
    
    if (empty($email)) {
        return array(
            'success' => false,
            'message' => 'Email not found in SAML response',
            'email' => null
        );
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array(
            'success' => false,
            'message' => 'Invalid email format in SAML response',
            'email' => null
        );
    }
    
    return array(
        'success' => true,
        'message' => 'Valid attributes',
        'email' => $email
    );
}

/**
 * Extract user attributes from SAML response
 * 
 * @param array $attributes SAML attributes
 * @return array User attributes (email, first_name, last_name, azure_id)
 */
function extractUserAttributes($attributes) {
    $userData = array(
        'email' => null,
        'first_name' => null,
        'last_name' => null,
        'azure_id' => null
    );
    
    // Email
    if (isset($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'])) {
        $emails = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'];
        if (is_array($emails) && count($emails) > 0) {
            $userData['email'] = $emails[0];
        }
    }
    
    // First Name
    if (isset($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'])) {
        $firstNames = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'];
        if (is_array($firstNames) && count($firstNames) > 0) {
            $userData['first_name'] = $firstNames[0];
        }
    }
    
    // Last Name
    if (isset($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'])) {
        $lastNames = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'];
        if (is_array($lastNames) && count($lastNames) > 0) {
            $userData['last_name'] = $lastNames[0];
        }
    }
    
    // Azure AD Object ID (optional)
    if (isset($attributes['http://schemas.microsoft.com/identity/claims/objectidentifier'])) {
        $objectIds = $attributes['http://schemas.microsoft.com/identity/claims/objectidentifier'];
        if (is_array($objectIds) && count($objectIds) > 0) {
            $userData['azure_id'] = $objectIds[0];
        }
    }
    
    return $userData;
}
?>
