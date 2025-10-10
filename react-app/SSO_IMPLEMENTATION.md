# SSO Implementation Guide

## Overview

This document describes the SSO (SAML 2.0) implementation for the DentWizard Apparel React application. The system supports **three authentication methods** that work simultaneously:

1. **Standard Login** - Existing PHP users with email/password
2. **SSO Login** - DentWizard employees via Azure AD SAML 2.0
3. **Development Mode** - Testing bypass

## Architecture

### Authentication Flow Diagram

```
User enters email
       ↓
   Detect auth type
       ↓
    ┌──────────────────┐
    │                  │
Standard          SSO (@dentwizard.com)
    │                  │
Password field    Microsoft button
    │                  │
PHP login         SAML redirect
    │                  │
    └──────┬───────────┘
           │
      JWT Token
           │
    React App (authenticated)
```

## Files Created

### 1. Configuration Files

#### `/src/config/samlConfig.js`
- Contains Azure AD SAML metadata
- Entity IDs, endpoints, certificate
- SSO domain detection (dentwizard.com)
- Helper functions for environment-based URLs

### 2. Services

#### `/src/services/ssoAuthService.js`
- `detectAuthType(email)` - Determines if user needs SSO
- `initiateSSO()` - Redirects to PHP SAML endpoint
- `handleSAMLCallback()` - Processes Azure AD response
- `logout()` - Handles SSO and standard logout
- Token and user data management

### 3. Components

#### `/src/pages/LoginPageSSO.js`
Enhanced login page supporting:
- Email detection with auth type determination
- Standard email/password login (existing users)
- SSO button for DentWizard employees
- Development mode quick login
- Backward compatible with existing auth

#### `/src/pages/auth/SSOCallbackPage.js`
Handles Azure AD return:
- Extracts token and user data from URL
- Validates SAML response
- Updates Redux store
- Redirects to intended destination
- Error handling and user feedback

### 4. Updated Files

#### `/src/App.js`
Added routes:
- `/login` - Updated to use SSO-enabled LoginPageSSO
- `/auth/sso-callback` - New SSO callback handler

#### `/.env`
Added SSO environment variables:
- `REACT_APP_SSO_ENTITY_ID`
- `REACT_APP_SSO_CALLBACK_URL`
- `REACT_APP_SSO_LOGOUT_URL`
- `REACT_APP_AZURE_TENANT_ID`

## Backend Requirements (PHP)

You need to create these PHP endpoints on your backend:

### 1. `/auth/check-user.php`
```php
// Check if user exists and determine auth type
{
  "exists": true,
  "auth_type": "sso" | "standard",
  "message": "User found"
}
```

### 2. `/auth/saml/login.php`
Initiates SAML authentication:
- Generates SAML AuthnRequest
- Signs request if needed
- Redirects to Azure AD login page
- Uses the configuration from LeaderGraphics.xml

### 3. `/auth/saml/callback.php`
Handles SAML response from Azure AD:
- Validates SAML assertion signature
- Extracts user attributes (email, name, etc.)
- Creates or updates user in database
- Generates JWT token
- Redirects to React app with token

**Redirect format:**
```
http://localhost:3000/auth/sso-callback?token=JWT_TOKEN&user=BASE64_USER_DATA
```

### 4. `/auth/saml/logout.php`
Handles SSO logout:
- Invalidates local session
- Generates SAML LogoutRequest
- Redirects to Azure AD logout
- Clears Azure AD session

## Environment Setup

### Development (.env)
```bash
REACT_APP_USE_MOCK_AUTH=true
REACT_APP_API_URL=https://dentwizard.lgstore.com/lg/API/v1
REACT_APP_SSO_ENTITY_ID=https://dentwizardapparel.com
REACT_APP_SSO_CALLBACK_URL=http://localhost:3000/auth/sso-callback
REACT_APP_SSO_LOGOUT_URL=http://localhost:3000/auth/sso-logout
REACT_APP_AZURE_TENANT_ID=ea1c5a3f-4d62-491a-8ba4-2e9955015493
```

### Production (.env.production)
```bash
REACT_APP_USE_MOCK_AUTH=false
REACT_APP_API_URL=https://dentwizard.lgstore.com/lg/API/v1
REACT_APP_SSO_ENTITY_ID=https://dentwizardapparel.com
REACT_APP_SSO_CALLBACK_URL=https://dentwizardapparel.com/api/auth/saml/callback
REACT_APP_SSO_LOGOUT_URL=https://dentwizardapparel.com/api/auth/saml/logout
REACT_APP_AZURE_TENANT_ID=ea1c5a3f-4d62-491a-8ba4-2e9955015493
```

## How It Works

### For Standard Users (Existing PHP Users)
1. User enters email (any non-@dentwizard.com)
2. System shows password field
3. User enters password
4. PHP validates against existing user database
5. Returns JWT token (existing flow)
6. User authenticated ✓

### For SSO Users (@dentwizard.com)
1. User enters @dentwizard.com email
2. System detects SSO requirement
3. Shows "Sign in with Microsoft" button
4. User clicks button
5. Redirects to `/auth/saml/login.php`
6. PHP generates SAML request → Azure AD
7. User authenticates with Microsoft credentials
8. Azure AD sends SAML response → `/auth/saml/callback.php`
9. PHP validates, creates JWT → redirects to React
10. React processes token → user authenticated ✓

## Testing Checklist

### Standard Login (Existing Users)
- [ ] Regular user can login with email/password
- [ ] Invalid password shows error
- [ ] Token stored correctly in localStorage
- [ ] User redirected to home page
- [ ] Logout works properly

### SSO Login (DentWizard Users)
- [ ] @dentwizard.com email triggers SSO
- [ ] Microsoft button appears
- [ ] Clicking redirects to Azure AD
- [ ] Azure AD login works
- [ ] Callback processes correctly
- [ ] User data extracted from SAML
- [ ] Token stored and user authenticated
- [ ] SSO logout clears both sessions

### Both Auth Methods
- [ ] Switching between email types works
- [ ] Both methods use same JWT format
- [ ] API calls work with both token types
- [ ] Budget and user data loads correctly
- [ ] Sessions persist across page refresh

## Security Considerations

1. **Certificate Expiration**: Azure AD certificate expires 2028-09-10
   - Set calendar reminder to renew before expiration
   
2. **SAML Validation**: PHP backend MUST validate:
   - SAML signature using Azure AD certificate
   - Assertion timestamps (NotBefore/NotOnOrAfter)
   - Audience restriction matches Entity ID
   - Issuer matches Azure AD Entity ID

3. **Token Security**:
   - JWT tokens should expire (8 hours recommended)
   - Use HTTPS in production
   - Validate tokens on every API request

## Troubleshooting

### SSO Button Doesn't Appear
- Check email domain detection in `ssoAuthService.js`
- Verify `ssoDomains` array in `samlConfig.js`
- Check browser console for errors

### SAML Callback Fails
- Verify callback URL matches in Azure AD and .env
- Check PHP backend SAML validation
- Look for certificate mismatch
- Check clock skew (allow 5 minutes tolerance)

### Standard Login Broken
- Ensure `/auth/login-token.php` endpoint unchanged
- Verify backward compatibility in authSlice
- Check localStorage keys match expected format

### Token Not Working
- Verify JWT format matches existing system
- Check token expiration
- Ensure auth interceptor in `api.js` unchanged

## Next Steps

### 1. Implement PHP Backend (Required)
Create these files in your PHP API:
```
/lg/API/v1/auth/
├── check-user.php          (NEW - detect auth type)
├── login-token.php         (EXISTING - keep unchanged)
└── saml/
    ├── login.php           (NEW - initiate SAML)
    ├── callback.php        (NEW - handle response)
    └── logout.php          (NEW - SSO logout)
```

### 2. Install PHP SAML Library
```bash
composer require onelogin/php-saml
```

### 3. Configure Azure AD
Provide DentWizard with:
- Entity ID: `https://dentwizardapparel.com`
- Reply URL: `https://dentwizardapparel.com/api/auth/saml/callback`
- Sign-on URL: `https://dentwizardapparel.com/login`

### 4. Test in Development
```bash
cd react-app
npm install
npm start
```

Test with:
- Existing user email → standard login
- @dentwizard.com email → SSO flow

### 5. Deploy to Production
1. Update .env.production with production URLs
2. Ensure PHP backend accessible at production domain
3. Test SSO flow end-to-end
4. Monitor for certificate expiration

## Support

For issues or questions:
1. Check browser console for errors
2. Review PHP backend logs
3. Verify Azure AD configuration
4. Check this documentation for troubleshooting
