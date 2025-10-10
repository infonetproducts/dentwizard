<?php
/**
 * SAML Callback Handler - PHP 5.6 Compatible
 * 
 * Processes SAML response from Azure AD and authenticates user
 * 
 * @endpoint POST /api/saml-callback.php
 */

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load dependencies
require_once dirname(__FILE__) . '/config/saml-config.php';
require_once dirname(__FILE__) . '/helpers/saml-helpers.php';

// Check if Composer autoloader exists
if (!file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    error_log('Composer dependencies not installed');
    redirectToReactError('SAML library not installed. Please contact administrator.');
}

require_once dirname(__FILE__) . '/vendor/autoload.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Get SAML configuration
    $samlSettings = getSAMLConfig();
    
    // Create SAML Auth object
    $auth = new OneLogin\Saml2\Auth($samlSettings);
    
    // Process SAML response
    $auth->processResponse();
    
    // Check for errors
    $errors = $auth->getErrors();
    if (!empty($errors)) {
        $errorMsg = implode(', ', $errors);
        error_log('SAML Response Errors: ' . $errorMsg);
        error_log('Last Error Reason: ' . $auth->getLastErrorReason());
        
        logSAMLAttempt('unknown', 'error', 'SAML validation failed: ' . $errorMsg);
        redirectToReactError('SAML authentication failed. Please try again.');
    }
    
    // Check if user is authenticated
    if (!$auth->isAuthenticated()) {
        logSAMLAttempt('unknown', 'error', 'User not authenticated after SAML response');
        redirectToReactError('Authentication failed. Please try again.');
    }
    
    // Get user attributes from SAML response
    $attributes = $auth->getAttributes();
    $nameId = $auth->getNameId(); // Usually the email
    
    // Validate attributes
    $validation = validateSAMLAttributes($attributes);
    if (!$validation['success']) {
        logSAMLAttempt('unknown', 'error', $validation['message']);
        redirectToReactError($validation['message']);
    }
    
    $email = $validation['email'];
    
    // If NameID is email and we didn't get email from attributes, use NameID
    if (empty($email) && !empty($nameId) && filter_var($nameId, FILTER_VALIDATE_EMAIL)) {
        $email = $nameId;
    }
    
    if (empty($email)) {
        logSAMLAttempt('unknown', 'error', 'No email found in SAML response');
        redirectToReactError('Email not found in Azure AD response');
    }
    
    // Extract user data
    $samlUserData = extractUserAttributes($attributes);
    if (empty($samlUserData['email'])) {
        $samlUserData['email'] = $email;
    }
    
    // Check if user exists in database
    $user = getUserByEmail($email);
    
    if (!$user) {
        logSAMLAttempt($email, 'error', 'User not found in database');
        redirectToReactError('User account not found. Please contact your administrator to create your account.');
    }
    
    // Check if user is active
    if ($user['is_active'] != 1) {
        logSAMLAttempt($email, 'error', 'User account is deactivated');
        redirectToReactError('Your account has been deactivated. Please contact your administrator.');
    }
    
    // Check auth type matches
    if ($user['auth_type'] !== 'sso') {
        logSAMLAttempt($email, 'error', 'User account is not configured for SSO');
        redirectToReactError('Your account is not configured for SSO authentication.');
    }
    
    // Update last login
    updateLastLogin($user['user_id']);
    
    // Update Azure AD Object ID if present and not already set
    if (!empty($samlUserData['azure_id']) && empty($user['azure_ad_object_id'])) {
        try {
            $conn = getDatabaseConnection();
            $userId = intval($user['user_id']);
            $azureId = mysqli_real_escape_string($conn, $samlUserData['azure_id']);
            
            $query = "UPDATE users SET azure_ad_object_id = '$azureId' WHERE user_id = $userId";
            mysqli_query($conn, $query);
            mysqli_close($conn);
            
        } catch (Exception $e) {
            error_log('Failed to update Azure AD Object ID: ' . $e->getMessage());
        }
    }
    
    // Generate JWT token
    $token = generateJWT($user);
    
    // Log successful authentication
    logSAMLAttempt($email, 'success', 'User authenticated successfully');
    
    // Clear session variables
    unset($_SESSION['saml_email']);
    unset($_SESSION['saml_initiated_at']);
    
    // Redirect to React app with token
    redirectToReactSuccess($token, $user);
    
} catch (Exception $e) {
    error_log('SAML Callback Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    logSAMLAttempt('unknown', 'error', 'Exception: ' . $e->getMessage());
    redirectToReactError('An error occurred during authentication. Please try again.');
}
?>
