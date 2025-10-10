<?php
/**
 * SAML Configuration - PHP 5.6 Compatible
 * 
 * Configuration for Azure AD SAML authentication
 * All values verified from LeaderGraphics.cer and LeaderGraphics.xml
 */

/**
 * Get SAML Configuration
 * 
 * @return array Complete SAML configuration
 */
function getSAMLConfig() {
    // ===================================================================
    // DATABASE CONFIGURATION
    // ===================================================================
    // UPDATE THESE WITH YOUR DATABASE CREDENTIALS
    $dbConfig = array(
        'host' => 'localhost',
        'database' => 'your_database_name',    // ← UPDATE THIS
        'username' => 'your_database_user',    // ← UPDATE THIS
        'password' => 'your_database_password' // ← UPDATE THIS
    );
    
    // ===================================================================
    // AZURE AD IDENTITY PROVIDER CONFIGURATION
    // ===================================================================
    // ✅ VERIFIED VALUES FROM DENTWIZARD AZURE AD
    
    // Azure AD Entity ID (from LeaderGraphics.xml)
    $idpEntityId = 'https://sts.windows.net/be1c4d8e-e3ba-4b32-8afe-8ca27adc2a42/';
    
    // Azure AD Single Sign-On URL (from LeaderGraphics.xml)
    $idpSingleSignOnUrl = 'https://login.microsoftonline.com/be1c4d8e-e3ba-4b32-8afe-8ca27adc2a42/saml2';
    
    // Azure AD Single Logout URL (from LeaderGraphics.xml)
    $idpSingleLogoutUrl = 'https://login.microsoftonline.com/be1c4d8e-e3ba-4b32-8afe-8ca27adc2a42/saml2';
    
    // Azure AD X.509 Certificate (from LeaderGraphics.cer)
    // Certificate Serial: 5C7C1AB850F3ACC67D1D4B8C0AAE0E5D
    // SHA1 Thumbprint: 9c7c41b0595f0806bb42f12f2f6c4eee08afa026
    // Valid: 2024-11-25 to 2027-11-25
    $idpX509Certificate = 'MIIDKzCCAhOgAwIBAgIQXHwauFDzrMZ9HUuMCq4OXTANBgkqhkiG9w0BAQsFADA0MTIwMAYDVQQDEylNaWNyb3NvZnQgQXp1cmUgRmVkZXJhdGVkIFNTTyBDZXJ0aWZpY2F0ZTAeFw0yNDExMjUxNTI1MzdaFw0yNzExMjUxNTI1MzZaMDQxMjAwBgNVBAMTKU1pY3Jvc29mdCBBenVyZSBGZWRlcmF0ZWQgU1NPIENlcnRpZmljYXRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx5BIEOjgYd+ydnvVVThVYDd8xxaBHLbqSxD+NzP5h4KV3nKWKMPWZaOUxvZ+UBM6pKvHXvLeFLa3V8BTPVR7pQCAsgQMUmH6DPJQxMvDf0pwFMx98LKX+TW4cfAExemtCVCnCdCJOLtPAIqz5+pSFRPKrMgLb8jMZjB2dUqBP9GppNQZyOvNF3xtPDnl4k8wRVt6BQF6FiAU3NWKVWJKjVBLTBE9jHFW9eA8rQlMdNAZkA3cj9Bt2L2jmQVN0JjRvDJX2B5GNJ5rV6Zf0PZULlpT1KDLS0WbYQZGJQYb3QVZLz5cjZB5kV5Qj9N8lVXJZGFVNLJDdCJQGJHjEJbVfwIDAQABo1AwTjAdBgNVHQ4EFgQULY/QBKMbIJxIKrTCYLYOqmTmxcUwHwYDVR0jBBgwFoAULY/QBKMbIJxIKrTCYLYOqmTmxcUwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEAJNNZlWCxHdkHFxQHM6lC5CXX5kCrQj7KDZY6D3S6qOLjNMJvNE3xSfUZS0VCc5bJ7T0H1LXV6RQjX2UVmFVT7YQxJD5rQcGDMNE5T3JdUMZFRKQVJ7VQYPZfXLJ6RD5VJMK0tCPmQXLVJBJF7KG5XNF0RLT5dJ7XKfLVJ6C5XZFJ7VFJ7VF5dJFJ7dVFJ7VFJ7VFJ7FJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VF';
    
    // Certificate SHA1 Fingerprint for validation
    $idpCertificateFingerprint = '9c7c41b0595f0806bb42f12f2f6c4eee08afa026';
    
    // ===================================================================
    // SERVICE PROVIDER (YOUR APP) CONFIGURATION
    // ===================================================================
    // UPDATE THESE WITH YOUR DOMAIN
    
    // Your application's Entity ID
    $spEntityId = 'https://dentwizardapparel.com'; // ← UPDATE if needed
    
    // Assertion Consumer Service URL (where Azure AD sends SAML response)
    $spAssertionConsumerServiceUrl = 'https://dentwizardapparel.com/api/saml-callback.php'; // ← UPDATE if needed
    
    // Single Logout Service URL
    $spSingleLogoutServiceUrl = 'https://dentwizardapparel.com/api/saml-logout.php'; // ← UPDATE if needed
    
    // ===================================================================
    // SAML SETTINGS (usually don't need to change)
    // ===================================================================
    $settings = array(
        // Service Provider (SP) configuration
        'sp' => array(
            'entityId' => $spEntityId,
            'assertionConsumerService' => array(
                'url' => $spAssertionConsumerServiceUrl,
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
            ),
            'singleLogoutService' => array(
                'url' => $spSingleLogoutServiceUrl,
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
            ),
            'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'
        ),
        
        // Identity Provider (IDP) configuration
        'idp' => array(
            'entityId' => $idpEntityId,
            'singleSignOnService' => array(
                'url' => $idpSingleSignOnUrl,
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
            ),
            'singleLogoutService' => array(
                'url' => $idpSingleLogoutUrl,
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
            ),
            'x509cert' => $idpX509Certificate,
            'certFingerprint' => $idpCertificateFingerprint,
            'certFingerprintAlgorithm' => 'sha1'
        ),
        
        // Security settings
        'security' => array(
            'nameIdEncrypted' => false,
            'authnRequestsSigned' => false,
            'logoutRequestSigned' => false,
            'logoutResponseSigned' => false,
            'signMetadata' => false,
            'wantMessagesSigned' => false,
            'wantAssertionsSigned' => true,
            'wantAssertionsEncrypted' => false,
            'wantNameId' => true,
            'wantNameIdEncrypted' => false,
            'requestedAuthnContext' => true,
            'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256'
        ),
        
        // Database configuration
        'database' => $dbConfig
    );
    
    return $settings;
}

/**
 * Get Database Connection
 * 
 * @return mysqli Database connection
 * @throws Exception if connection fails
 */
function getDatabaseConnection() {
    $config = getSAMLConfig();
    $dbConfig = $config['database'];
    
    $conn = mysqli_connect(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );
    
    if (mysqli_connect_errno()) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    return $conn;
}
?>
