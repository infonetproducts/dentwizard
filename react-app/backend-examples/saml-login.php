<?php
/**
 * SAML Login Initiator - PHP 5.6 Compatible
 * 
 * Initiates SAML authentication flow by redirecting to Azure AD
 * 
 * @endpoint GET /api/saml-login.php?email=user@dentwizard.com
 */

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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
    error_log('Composer dependencies not installed. Run: composer install');
    redirectToReactError('SAML library not installed. Please contact administrator.');
}

require_once dirname(__FILE__) . '/vendor/autoload.php';

try {
    // Get email parameter
    $email = isset($_GET['email']) ? trim($_GET['email']) : '';
    
    if (empty($email)) {
        logSAMLAttempt('unknown', 'error', 'No email provided');
        redirectToReactError('Email parameter is required');
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logSAMLAttempt($email, 'error', 'Invalid email format');
        redirectToReactError('Invalid email format');
    }
    
    // Check if email requires SSO
    if (!requiresSSO($email)) {
        logSAMLAttempt($email, 'error', 'Not an SSO domain');
        redirectToReactError('This email does not require SSO authentication');
    }
    
    // Log attempt
    logSAMLAttempt($email, 'initiated', 'Redirecting to Azure AD');
    
    // Get SAML configuration
    $samlSettings = getSAMLConfig();
    
    // Create SAML Auth object
    $auth = new OneLogin\Saml2\Auth($samlSettings);
    
    // Store email in session for callback
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['saml_email'] = $email;
    $_SESSION['saml_initiated_at'] = time();
    
    // Initiate SAML login (redirects to Azure AD)
    $auth->login();
    
} catch (Exception $e) {
    error_log('SAML Login Error: ' . $e->getMessage());
    logSAMLAttempt(isset($email) ? $email : 'unknown', 'error', $e->getMessage());
    redirectToReactError('An error occurred during SSO login. Please try again.');
}
?>
