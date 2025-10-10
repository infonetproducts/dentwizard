<?php
/**
 * SAML Configuration for OneLogin PHP-SAML library
 * 
 * This configuration matches the Azure AD metadata from LeaderGraphics.xml
 * and the React app's samlConfig.js
 */

$samlSettings = [
    // Service Provider (Your Application) Configuration
    'sp' => [
        // Entity ID - must match what you provided to DentWizard
        'entityId' => 'https://dentwizardapparel.com',
        
        // Assertion Consumer Service URL - where Azure AD sends the response
        'assertionConsumerService' => [
            'url' => 'https://dentwizardapparel.com/api/auth/saml/callback',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
        ],
        
        // Single Logout Service URL
        'singleLogoutService' => [
            'url' => 'https://dentwizardapparel.com/api/auth/saml/logout',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
        ],
        
        // Name ID Format
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
        
        // Optional: X.509 certificate for signing (if needed)
        'x509cert' => '',
        'privateKey' => '',
    ],
    
    // Identity Provider (Azure AD) Configuration
    'idp' => [
        // Azure AD Entity ID (from LeaderGraphics.xml)
        'entityId' => 'https://sts.windows.net/ea1c5a3f-4d62-491a-8ba4-2e9955015493/',
        
        // Single Sign-On Service URL
        'singleSignOnService' => [
            'url' => 'https://login.microsoftonline.com/ea1c5a3f-4d62-491a-8ba4-2e9955015493/saml2',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
        ],
        
        // Single Logout Service URL
        'singleLogoutService' => [
            'url' => 'https://login.microsoftonline.com/ea1c5a3f-4d62-491a-8ba4-2e9955015493/saml2',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
        ],
        
        // X.509 Certificate from Azure AD (LeaderGraphics.cer)
        // This is used to validate the SAML assertions
        'x509cert' => 'MIIC8DCCAdigAwIBAgIQMPh7mK8eJJNIeQfalXPrnDANBgkqhkiG9w0BAQsFADA0MTIwMAYDVQQDEylNaWNyb3NvZnQgQXp1cmUgRmVkZXJhdGVkIFNTTyBDZXJ0aWZpY2F0ZTAeFw0yNTA5MTAxNDE2MDlaFw0yODA5MTAxNDE2MDlaMDQxMjAwBgNVBAMTKU1pY3Jvc29mdCBBenVyZSBGZWRlcmF0ZWQgU1NPIENlcnRpZmljYXRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsdLw/KV24MfNYx1UkIkL+O0rlj0EFlJBVrOE5F/rfYeQ+sQPQlryZrAG1moks1rq8Wk/G0vVT7+XKqwUiDWWMdEuOmaWRqIuMTIYFhYuGuok2HasTD8lOzW5n6NC2v9nbfvTzVRutBRDdDQNmUpS5qGOrvA24l49SwP5PlPwvy1Wm1sNz496GPSWnpyOLPf6Y7uBSA+v7yHK0VBLSRU7+StvURPkrZ3PwpzIHiGz/dR6o4RXRW74HR6oFibZB73ZTq3SxCSzEaBURx1A0Hy7D9cYQ9Ml5z5RqGnhz68yjukcgGvy5jeHKuCYNp5nR81+bO3zHlnqoJEOt+1/nfX4ZQIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQAsLVfvY2U+o2iKZkCo8bN4+v4hYWYMzT/fCPVfS5quL6Vhy60zQe1os/kB2HEXcYFf/iHS2sXvlQaFJAW5AN0P0uObj/6xNDVNiAv2dLMU/nd7J9BXIPA9lc9RcCyeA2Y02Y0Jg636miXa76KpkG2gWYuvE26Y3O5uKr46bJu8gAflsT7fcnovTOqefiP+4drk+7hgxTS0jF8LIdD4deMhC8XHiyNWf+cvplFPRZZtjQ2ViB0ptwK2uox1SL+PtQ9E9zSvSbH19eXZC73ripFPXv30Hb7rn7gnf8jCuA+s8rsQVuLIJhs3LMu5iwH6kYGDTXZDd+pgPPBv4jYUtSiz',
        
        // IMPORTANT: Certificate expires 2028-09-10 - Monitor and renew!
    ],
    
    // Security Settings
    'security' => [
        // Require signed SAML assertions from Azure AD
        'wantAssertionsSigned' => true,
        
        // Don't require encrypted assertions
        'wantAssertionsEncrypted' => false,
        
        // Signature algorithm
        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
        
        // Digest algorithm
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
    ],
];
?>
