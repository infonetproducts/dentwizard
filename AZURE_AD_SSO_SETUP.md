# Azure AD SSO Setup for DentWizard Apparel Store

## Overview
Since DentWizard uses Microsoft 365, we'll integrate with Azure Active Directory (Azure AD / Microsoft Entra ID) for Single Sign-On. This allows your 3,000 employees to login using their existing Microsoft work accounts.

## Benefits for DentWizard
- **No additional cost** - Included with Microsoft 365
- **Familiar login** - Employees use their Outlook/Teams credentials
- **Automatic management** - When employees leave, access is revoked
- **Enterprise security** - Inherits company MFA and security policies
- **Audit trails** - All logins tracked in Azure AD

---

## SECTION 1: Information Needed from Client

### Email Template to Send
```
Subject: Azure AD Setup for Apparel Store SSO

Hi [Client Name],

Since DentWizard uses Microsoft 365, we'll integrate the apparel store with Azure AD for employee login. This means staff will use their existing Microsoft work accounts.

I need your IT administrator to create an App Registration in Azure AD and provide:

1. Tenant ID: _________________________
2. Client ID: _________________________  
3. Client Secret: _________________________
4. Your Azure domain: _________.onmicrosoft.com

I've attached step-by-step instructions for your IT team. The setup takes about 10 minutes and doesn't affect any existing systems.

Alternatively, we can schedule a brief call to walk through it together.

Please let me know if you have any questions.

Best regards,
[Your name]
```

---

## SECTION 2: Instructions for DentWizard IT Team

### Prerequisites
- Azure AD admin access (Global Administrator or Application Administrator role)
- Access to Azure Portal (portal.azure.com)

### Step-by-Step Setup

#### 1. Access Azure Portal
- Navigate to https://portal.azure.com
- Sign in with admin credentials
- Search for "Azure Active Directory" in the top search bar

#### 2. Create App Registration
1. Click **App registrations** in left menu
2. Click **+ New registration**
3. Configure as follows:
   ```
   Name: DentWizard Apparel Store
   
   Supported account types: 
   ✓ Accounts in this organizational directory only (DentWizard only - Single tenant)
   
   Redirect URI:
   Platform: Single-page application (SPA)
   URL: https://[your-render-app].onrender.com/auth/callback
   
   Also add (for local testing):
   http://localhost:3000/auth/callback
   ```
4. Click **Register**

#### 3. Save Important Values
After registration, you'll see the overview page. Copy these values:

- **Application (client) ID**: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
- **Directory (tenant) ID**: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx

#### 4. Create Client Secret
1. In left menu, click **Certificates & secrets**
2. Click **+ New client secret**
3. Configure:
   ```
   Description: DentWizard API Access
   Expires: 24 months (or your preference)
   ```
4. Click **Add**
5. **IMMEDIATELY COPY** the Value (it won't show again!)
   - Secret Value: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

#### 5. Configure API Permissions
1. In left menu, click **API permissions**
2. Click **+ Add a permission**
3. Choose **Microsoft Graph**
4. Choose **Delegated permissions**
5. Select these permissions:
   - `email` - View users' email address
   - `offline_access` - Maintain access to data
   - `openid` - Sign users in
   - `profile` - View users' basic profile
   - `User.Read` - Sign in and read user profile
6. Click **Add permissions**
7. Click **Grant admin consent for DentWizard** (important!)
8. Confirm by clicking **Yes**

#### 6. Configure Authentication (Optional but Recommended)
1. In left menu, click **Authentication**
2. Under **Implicit grant and hybrid flows**:
   - ✓ Check "Access tokens"
   - ✓ Check "ID tokens"
3. Under **Advanced settings**:
   - Allow public client flows: **No**
4. Click **Save**

#### 7. Add Additional Redirect URIs (if needed)
For production and staging environments:
```
https://apparel.dentwizard.com/auth/callback
https://staging-apparel.dentwizard.com/auth/callback
https://dentwizard-apparel.onrender.com/auth/callback
```

---

## SECTION 3: Developer Configuration

### Environment Variables (.env)
```bash
# Azure AD Configuration
SSO_PROVIDER=azure
AZURE_TENANT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
AZURE_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx  
AZURE_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
AZURE_REDIRECT_URI=https://your-app.onrender.com/auth/callback

# Azure AD Endpoints (auto-constructed from tenant ID)
AZURE_AUTHORITY=https://login.microsoftonline.com/{AZURE_TENANT_ID}
AZURE_LOGOUT_URI=https://login.microsoftonline.com/{AZURE_TENANT_ID}/oauth2/v2.0/logout
```

### React App Configuration

#### Install Dependencies
```bash
npm install @azure/msal-browser @azure/msal-react
```

#### authConfig.js
```javascript
export const msalConfig = {
  auth: {
    clientId: process.env.REACT_APP_AZURE_CLIENT_ID,
    authority: `https://login.microsoftonline.com/${process.env.REACT_APP_AZURE_TENANT_ID}`,
    redirectUri: process.env.REACT_APP_REDIRECT_URI || "http://localhost:3000/auth/callback",
    postLogoutRedirectUri: process.env.REACT_APP_BASE_URL || "http://localhost:3000"
  },
  cache: {
    cacheLocation: "sessionStorage", // or "localStorage"
    storeAuthStateInCookie: false, // Set to true for IE11/Edge
  }
};

export const loginRequest = {
  scopes: ["User.Read", "openid", "profile", "email"]
};
```

#### App.js Integration
```javascript
import { MsalProvider, useMsal, useIsAuthenticated } from "@azure/msal-react";
import { PublicClientApplication } from "@azure/msal-browser";
import { msalConfig, loginRequest } from "./authConfig";

const msalInstance = new PublicClientApplication(msalConfig);

function App() {
  return (
    <MsalProvider instance={msalInstance}>
      <AppContent />
    </MsalProvider>
  );
}

function LoginButton() {
  const { instance } = useMsal();
  
  const handleLogin = () => {
    instance.loginPopup(loginRequest).then(response => {
      // Send token to PHP API for validation
      validateWithAPI(response.idToken);
    }).catch(error => {
      console.error(error);
    });
  };
  
  return (
    <button onClick={handleLogin}>
      Sign in with Microsoft
    </button>
  );
}
```

### PHP API Integration

#### Update v1/auth/validate.php
```php
<?php
// Azure AD token validation
require_once '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['id_token'];

// Get Azure AD public keys
$tenant_id = getenv('AZURE_TENANT_ID');
$keys_url = "https://login.microsoftonline.com/{$tenant_id}/discovery/v2.0/keys";
$keys = json_decode(file_get_contents($keys_url), true);

// Decode and validate token
try {
    $decoded = JWT::decode($token, JWK::parseKeySet($keys), ['RS256']);
    
    // Extract user info
    $email = $decoded->email;
    $name = $decoded->name;
    $azure_id = $decoded->sub;
    
    // Create/update user in database
    // Set session variables
    // Return success response
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
}
?>
```

---

## SECTION 4: Testing

### Test Accounts
Request test accounts from DentWizard IT:
- Regular employee account
- Manager/admin account  
- Account with budget restrictions

### Testing Checklist
- [ ] Login popup appears with Microsoft branding
- [ ] Can enter DentWizard email (xxx@dentwizard.com)
- [ ] Redirects back to app after login
- [ ] Token validates successfully in PHP API
- [ ] User session created properly
- [ ] Budget information loads correctly
- [ ] Logout clears session completely
- [ ] Login works from different browsers
- [ ] Mobile login works properly

### Test URLs
```
Local: http://localhost:3000
Staging: https://staging-apparel.dentwizard.com
Production: https://apparel.dentwizard.com
```

---

## SECTION 5: Troubleshooting

### Common Issues and Solutions

#### "AADSTS50011: Reply URL mismatch"
**Cause:** Redirect URI doesn't match exactly
**Solution:** Ensure URLs match exactly in Azure AD and app (including https://, trailing slashes)

#### "AADSTS65001: User or admin has not consented"
**Cause:** Admin consent not granted
**Solution:** Admin must click "Grant admin consent" in Azure Portal > API permissions

#### "Invalid token signature"
**Cause:** Token validation failing
**Solution:** 
- Verify tenant ID is correct
- Check server time is synchronized
- Ensure using correct public keys endpoint

#### "User cannot login"
**Cause:** User not in correct directory
**Solution:** Verify user is in DentWizard Azure AD tenant

#### "Token expired" immediately
**Cause:** Server time mismatch
**Solution:** Sync server time with NTP

#### "CORS error on login"
**Cause:** Redirect URI not configured
**Solution:** Add all app URLs to Azure AD redirect URIs

---

## SECTION 6: Security Best Practices

### Required Security Measures
1. **Always use HTTPS** in production
2. **Validate token expiration** (`exp` claim)
3. **Verify token audience** (`aud` claim = your client ID)
4. **Check token issuer** (`iss` claim)
5. **Implement rate limiting** on validation endpoint
6. **Log all authentication events** for auditing
7. **Use secure session storage** (encrypted cookies)
8. **Implement session timeout** (match company policy)

### Session Management
```php
// Recommended session configuration
ini_set('session.cookie_secure', true);    // HTTPS only
ini_set('session.cookie_httponly', true);  // No JS access
ini_set('session.cookie_samesite', 'Lax'); // CSRF protection
ini_set('session.gc_maxlifetime', 3600);   // 1 hour timeout
```

---

## SECTION 7: Production Deployment

### Pre-Launch Checklist
- [ ] Production redirect URIs added in Azure AD
- [ ] Client secret stored securely (environment variable)
- [ ] HTTPS configured on all endpoints
- [ ] Rate limiting enabled on API
- [ ] Error logging configured
- [ ] Session management tested
- [ ] Backup authentication method (if required)

### Environment-Specific Configuration
```javascript
// React environment variables
REACT_APP_AZURE_CLIENT_ID=prod-client-id
REACT_APP_AZURE_TENANT_ID=prod-tenant-id
REACT_APP_REDIRECT_URI=https://apparel.dentwizard.com/auth/callback
REACT_APP_API_URL=https://api.dentwizard.com/v1
```

### Monitoring
- Azure AD Sign-in logs
- API authentication failures
- Session creation/destruction
- Token validation performance

---

## SECTION 8: User Documentation

### For DentWizard Employees

#### How to Login
1. Navigate to https://apparel.dentwizard.com
2. Click "Sign in with Microsoft"
3. Enter your work email (yourname@dentwizard.com)
4. Enter your work password
5. Complete MFA if prompted
6. You're now logged in!

#### Troubleshooting for Users
- **Can't login?** Use the same credentials as Outlook/Teams
- **Forgot password?** Contact IT help desk
- **Access denied?** Verify you have apparel ordering permissions
- **Session expired?** Simply login again

---

## SECTION 9: Support Contacts

### During Development
- Azure AD Documentation: https://docs.microsoft.com/azure/active-directory
- MSAL.js Documentation: https://github.com/AzureAD/microsoft-authentication-library-for-js
- JWT Debugger: https://jwt.ms (Microsoft's JWT decoder)

### For DentWizard
- IT Admin Contact: _________________
- Azure AD Admin: _________________
- Escalation: _________________

---

## SECTION 10: Migration Plan

### Phase 1: Pilot (Week 1)
- Deploy to staging environment
- Test with IT team (5-10 users)
- Gather feedback

### Phase 2: Soft Launch (Week 2)
- Deploy to production
- Enable for one department (~100 users)
- Monitor for issues

### Phase 3: Full Rollout (Week 3)
- Enable for all 3,000 users
- Communication to all employees
- IT support ready

### Phase 4: Optimization (Week 4+)
- Review login metrics
- Optimize performance
- Address user feedback

---

## Appendix A: Quick Reference

### Key URLs
```
Azure Portal: https://portal.azure.com
App Registration: Azure AD > App registrations > DentWizard Apparel Store
Sign-in Logs: Azure AD > Sign-in logs
Test Token: https://jwt.ms
```

### Required Values Checklist
- [ ] Tenant ID obtained
- [ ] Client ID obtained
- [ ] Client Secret obtained
- [ ] Redirect URIs configured
- [ ] Admin consent granted
- [ ] Test account created

### Emergency Rollback
If SSO fails in production:
1. Set `SSO_ENABLED=false` in environment
2. Fallback to session-based auth
3. Investigate Azure AD logs
4. Contact Microsoft support if needed

---

*Document Version: 1.0*
*Last Updated: Current*
*For: DentWizard Apparel Store SSO Implementation*