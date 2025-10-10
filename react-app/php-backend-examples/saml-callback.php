<?php
/**
 * SAML Callback Handler - Pre-Provisioned Users Only
 * 
 * This endpoint handles the SAML response from Azure AD after successful authentication.
 * Users MUST already exist in the database before they can log in via SSO.
 * 
 * Flow:
 * 1. Receive SAML response from Azure AD
 * 2. Validate SAML assertion
 * 3. Extract user email from SAML
 * 4. Check if user exists in database
 * 5. Verify user is SSO-enabled
 * 6. Generate JWT token
 * 7. Redirect to React app with token
 */

require_once __DIR__ . '/helpers/saml-helpers.php';

// Start session for SAML library
session_start();

// Get database connection
$pdo = getDatabaseConnection();

try {
    // Initialize SAML Auth
    $auth = initializeSAMLAuth();
    
    // Validate SAML response
    $userInfo = validateSAMLResponse($auth);
    
    if (!$userInfo) {
        logSSOAttempt('unknown', false, 'SAML validation failed');
        redirectToReactWithError('Invalid SAML response. Please try again.');
    }
    
    $email = $userInfo['email'];
    
    // CRITICAL: Check if user exists in database
    $user = getUserByEmail($email);
    
    if (!$user) {
        // User does NOT exist - reject login
        logSSOAttempt($email, false, 'User not found in database');
        redirectToReactWithError(
            'Your account has not been set up yet. Please contact your administrator to create your account.'
        );
    }
    
    // Verify user is configured for SSO
    if (!isUserSSOEnabled($user)) {
        logSSOAttempt($email, false, 'User not configured for SSO');
        redirectToReactWithError(
            'Your account is not configured for SSO login. Please use the standard login form.'
        );
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        logSSOAttempt($email, false, 'User account is deactivated');
        redirectToReactWithError(
            'Your account has been deactivated. Please contact your administrator.'
        );
    }
    
    // Update user's Azure AD Object ID if not already set
    if (empty($user['azure_ad_object_id']) && !empty($userInfo['object_id'])) {
        updateAzureObjectId($user['user_id'], $userInfo['object_id']);
        $user['azure_ad_object_id'] = $userInfo['object_id'];
    }
    
    // Update last login timestamp
    updateLastLogin($user['user_id']);
    
    // Generate JWT token
    $token = generateJWT($user);
    
    // Log successful authentication
    logSSOAttempt($email, true);
    
    // Redirect to React app with token and user data
    $reactUrl = getenv('REACT_APP_URL') ?: 'http://localhost:3000';
    redirectToReact($token, $user, $reactUrl);
    
} catch (Exception $e) {
    error_log("SAML callback error: " . $e->getMessage());
    logSSOAttempt('unknown', false, $e->getMessage());
    redirectToReactWithError('An error occurred during authentication. Please try again.');
}
