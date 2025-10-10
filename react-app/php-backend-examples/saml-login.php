<?php
/**
 * SAML SSO Login Initiator
 * 
 * Generates SAML AuthnRequest and redirects to Azure AD
 * 
 * Endpoint: /lg/API/v1/auth/saml/login.php
 * Method: GET
 * Query: ?returnUrl=/dashboard
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/saml-config.php';

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Utils;

// Store return URL in session for after authentication
session_start();
$_SESSION['saml_return_url'] = $_GET['returnUrl'] ?? '/';

try {
    // Initialize SAML Auth with configuration
    $auth = new Auth($samlSettings);
    
    // Initiate SAML SSO
    // This will redirect the user to Azure AD login page
    $auth->login();
    
    // Redirect happens above, but if it fails:
    error_log('SAML login redirect failed');
    header('Location: /login?error=sso_init_failed');
    exit;
    
} catch (Exception $e) {
    error_log('SAML login error: ' . $e->getMessage());
    header('Location: /login?error=sso_error&message=' . urlencode($e->getMessage()));
    exit;
}
?>
