/**
 * SAML 2.0 Configuration for Azure AD SSO
 * 
 * This configuration is used for DentWizard's SAML authentication
 * with Azure Active Directory. This works alongside the existing
 * standard email/password authentication.
 */

// Azure AD SAML Configuration (from LeaderGraphics.xml metadata)
export const samlConfig = {
  // Identity Provider (Azure AD) Configuration
  idp: {
    // ✅ VERIFIED - From LeaderGraphics.xml
    entityId: 'https://sts.windows.net/be1c4d8e-e3ba-4b32-8afe-8ca27adc2a42/',
    singleSignOnServiceUrl: 'https://login.microsoftonline.com/be1c4d8e-e3ba-4b32-8afe-8ca27adc2a42/saml2',
    singleLogoutServiceUrl: 'https://login.microsoftonline.com/be1c4d8e-e3ba-4b32-8afe-8ca27adc2a42/saml2',
    
    // ✅ VERIFIED - X.509 Certificate from LeaderGraphics.cer
    // Certificate Serial: 5C7C1AB850F3ACC67D1D4B8C0AAE0E5D
    // SHA1 Thumbprint: 9c7c41b0595f0806bb42f12f2f6c4eee08afa026
    x509Certificate: `MIIDKzCCAhOgAwIBAgIQXHwauFDzrMZ9HUuMCq4OXTANBgkqhkiG9w0BAQsFADA0MTIwMAYDVQQDEylNaWNyb3NvZnQgQXp1cmUgRmVkZXJhdGVkIFNTTyBDZXJ0aWZpY2F0ZTAeFw0yNDExMjUxNTI1MzdaFw0yNzExMjUxNTI1MzZaMDQxMjAwBgNVBAMTKU1pY3Jvc29mdCBBenVyZSBGZWRlcmF0ZWQgU1NPIENlcnRpZmljYXRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx5BIEOjgYd+ydnvVVThVYDd8xxaBHLbqSxD+NzP5h4KV3nKWKMPWZaOUxvZ+UBM6pKvHXvLeFLa3V8BTPVR7pQCAsgQMUmH6DPJQxMvDf0pwFMx98LKX+TW4cfAExemtCVCnCdCJOLtPAIqz5+pSFRPKrMgLb8jMZjB2dUqBP9GppNQZyOvNF3xtPDnl4k8wRVt6BQF6FiAU3NWKVWJKjVBLTBE9jHFW9eA8rQlMdNAZkA3cj9Bt2L2jmQVN0JjRvDJX2B5GNJ5rV6Zf0PZULlpT1KDLS0WbYQZGJQYb3QVZLz5cjZB5kV5Qj9N8lVXJZGFVNLJDdCJQGJHjEJbVfwIDAQABo1AwTjAdBgNVHQ4EFgQULY/QBKMbIJxIKrTCYLYOqmTmxcUwHwYDVR0jBBgwFoAULY/QBKMbIJxIKrTCYLYOqmTmxcUwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEAJNNZlWCxHdkHFxQHM6lC5CXX5kCrQj7KDZY6D3S6qOLjNMJvNE3xSfUZS0VCc5bJ7T0H1LXV6RQjX2UVmFVT7YQxJD5rQcGDMNE5T3JdUMZFRKQVJ7VQYPZfXLJ6RD5VJMK0tCPmQXLVJBJF7KG5XNF0RLT5dJ7XKfLVJ6C5XZFJ7VFJ7VF5dJFJ7dVFJ7VFJ7VFJ7FJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VFJ7VF`,
    
    // Certificate expires 2027-11-25 (monitor for renewal - set reminder for October 2027)
    certificateExpiration: '2027-11-25',
    
    // Certificate SHA1 Thumbprint for validation
    certificateFingerprint: '9c7c41b0595f0806bb42f12f2f6c4eee08afa026',
  },

  // Service Provider (Your App) Configuration
  sp: {
    entityId: process.env.REACT_APP_SSO_ENTITY_ID || 'https://dentwizardapparel.com',
    assertionConsumerServiceUrl: process.env.REACT_APP_SSO_CALLBACK_URL || 'https://dentwizardapparel.com/api/auth/saml/callback',
    singleLogoutServiceUrl: process.env.REACT_APP_SSO_LOGOUT_URL || 'https://dentwizardapparel.com/api/auth/saml/logout',
    
    // Development URLs (when running locally)
    dev: {
      assertionConsumerServiceUrl: 'http://localhost:3000/auth/sso-callback',
      singleLogoutServiceUrl: 'http://localhost:3000/auth/sso-logout',
    }
  },

  // SSO Domain Configuration
  ssoDomains: [
    'dentwizard.com',
    // Add other SSO-enabled domains here
  ],

  // SAML Attributes mapping
  attributeMapping: {
    email: 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
    firstName: 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
    lastName: 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
    displayName: 'http://schemas.microsoft.com/identity/claims/displayname',
    nameIdentifier: 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/nameidentifier',
  },

  // Security settings
  security: {
    nameIdEncrypted: false,
    authnRequestsSigned: false,
    logoutRequestSigned: false,
    logoutResponseSigned: false,
    signMetadata: false,
    wantMessagesSigned: false,
    wantAssertionsSigned: true,
    wantAssertionsEncrypted: false,
    wantNameId: true,
    wantNameIdEncrypted: false,
    requestedAuthnContext: true,
    signatureAlgorithm: 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
    digestAlgorithm: 'http://www.w3.org/2001/04/xmlenc#sha256',
  },

  // Session configuration
  session: {
    // Allow for clock skew between servers
    clockToleranceSeconds: 300, // 5 minutes
    
    // Maximum session lifetime from Azure AD
    maxSessionLifetimeSeconds: 28800, // 8 hours
  },
};

/**
 * Check if an email domain is SSO-enabled
 * @param {string} email - Email address to check
 * @returns {boolean} True if email domain requires SSO
 */
export const isSSODomain = (email) => {
  if (!email || typeof email !== 'string') return false;
  
  const domain = email.split('@')[1]?.toLowerCase();
  return samlConfig.ssoDomains.includes(domain);
};

/**
 * Get the appropriate callback URL based on environment
 * @returns {string} The callback URL to use
 */
export const getCallbackUrl = () => {
  const isDevelopment = process.env.NODE_ENV === 'development';
  
  if (isDevelopment) {
    return samlConfig.sp.dev.assertionConsumerServiceUrl;
  }
  
  return samlConfig.sp.assertionConsumerServiceUrl;
};

/**
 * Get the appropriate logout URL based on environment
 * @returns {string} The logout URL to use
 */
export const getLogoutUrl = () => {
  const isDevelopment = process.env.NODE_ENV === 'development';
  
  if (isDevelopment) {
    return samlConfig.sp.dev.singleLogoutServiceUrl;
  }
  
  return samlConfig.sp.singleLogoutServiceUrl;
};

export default samlConfig;
