# DentWizard Authentication System Documentation

## Current Development Setup (Hardcoded User)

### Overview
Currently, the application uses a hardcoded user (Joe Lorenzo, ID: 19346) for development purposes to bypass authentication during testing.

### Hardcoded User Locations

#### 1. API Endpoints (PHP)
- **File**: `/API/v1/user/profile-dev.php`
  ```php
  $user_id = isset($_GET['id']) ? intval($_GET['id']) : 19346; // Default to Joe Lorenzo
  ```

- **File**: `/API/v1/orders/my-orders-dev.php`
  ```php
  $user_id = isset($_GET['id']) ? intval($_GET['id']) : 19346; // Default to Joe Lorenzo
  ```

#### 2. Database Connection
- **User**: Joe Lorenzo
- **User ID**: 19346
- **Email**: joseph.lorenzo@dentwizard.com
- **Client ID**: 244

### Why Development Mode?
- Allows testing without authentication complexity
- Speeds up development cycle
- Mimics production data structure without auth overhead

---

## Proposed Dual Authentication System

### Architecture Overview

```
User Login Flow:
     ↓
[Login Page]
     ↓
Check User Type
     ├─→ SSO User → [Azure AD Authentication]
     └─→ Standard User → [PHP Session Authentication]
                ↓
         [Unified Token/Session]
                ↓
            [React App]
```

### Database Schema Addition

Add to Users table:
```sql
ALTER TABLE Users ADD COLUMN auth_type ENUM('standard', 'sso', 'both') DEFAULT 'standard';
ALTER TABLE Users ADD COLUMN azure_ad_id VARCHAR(255) NULL;
```

### Authentication Flow

#### 1. Login Page Logic
```javascript
// Check user type first
const checkAuthType = async (email) => {
  const response = await api.post('/auth/check-type', { email });
  return response.data.auth_type; // 'sso', 'standard', or 'both'
};

// Route to appropriate login
if (authType === 'sso') {
  // Trigger Azure AD login
  msalInstance.loginPopup();
} else {
  // Show standard login form
  showPasswordField();
}
```

#### 2. Backend Authentication Endpoints

**Check Auth Type** (`/auth/check-type.php`):
```php
<?php
$email = $_POST['email'];
$sql = "SELECT auth_type FROM Users WHERE Email = ?";
// Return: { auth_type: 'sso' | 'standard' | 'both' }
```

**Standard Login** (`/auth/login.php`):
```php
<?php
session_start();
$email = $_POST['email'];
$password = $_POST['password'];

// Verify credentials against Users table
// Create session and/or JWT token
$_SESSION['user_id'] = $user['ID'];
$_SESSION['auth_method'] = 'standard';

// Return unified response
return [
  'token' => $jwt_token,
  'user' => $user_data,
  'auth_method' => 'standard'
];
```

**SSO Login Verification** (`/auth/verify-sso.php`):
```php
<?php
// Verify Azure AD token
$azure_token = $_POST['azure_token'];
// Validate with Microsoft Graph API
// Match azure_ad_id with Users table
// Create session/JWT
```

---

## Implementation Plan - Next Steps

### Phase 1: Database Preparation (Week 1)
1. **Add auth columns to Users table**
   - `auth_type` field
   - `azure_ad_id` field
   - `password_hash` field (if not exists)

2. **Set initial auth types**
   ```sql
   UPDATE Users SET auth_type = 'standard' WHERE password_hash IS NOT NULL;
   UPDATE Users SET auth_type = 'sso' WHERE azure_ad_id IS NOT NULL;
   ```

### Phase 2: Create Authentication API (Week 1-2)
1. **Create `/API/v1/auth/` directory**
2. **Implement endpoints**:
   - `check-type.php` - Check user auth method
   - `login.php` - Standard authentication
   - `verify-sso.php` - SSO token verification
   - `refresh.php` - Token refresh
   - `logout.php` - Clear session/token

### Phase 3: Create Login Page (Week 2)
1. **Create React Login Component**
   ```jsx
   // src/pages/LoginPage.js
   - Email input field
   - Dynamic form based on auth_type
   - Password field (standard users)
   - SSO button (SSO users)
   - "Remember me" option
   ```

2. **Implement Auth Service**
   ```javascript
   // src/services/authService.js
   - checkAuthType(email)
   - standardLogin(email, password)
   - ssoLogin()
   - handleAuthResponse()
   ```

### Phase 4: Update Existing APIs (Week 3)
1. **Replace dev endpoints with production versions**
   - `profile-dev.php` → `profile.php`
   - `my-orders-dev.php` → `my-orders.php`

2. **Update middleware/auth.php**
   - Support both session and JWT
   - Validate based on auth_method

### Phase 5: Testing & Migration (Week 3-4)
1. **Test both authentication flows**
2. **Migrate existing users**
3. **Set up user management interface**

---

## Configuration Files Needed

### 1. Auth Configuration (`/API/config/auth.php`)
```php
<?php
return [
    'jwt_secret' => getenv('JWT_SECRET'),
    'session_lifetime' => 7200, // 2 hours
    'azure_ad' => [
        'tenant_id' => getenv('AZURE_TENANT_ID'),
        'client_id' => getenv('AZURE_CLIENT_ID'),
        'client_secret' => getenv('AZURE_CLIENT_SECRET')
    ]
];
```

### 2. React Auth Config (`src/config/auth.js`)
```javascript
export const authConfig = {
  apiBaseUrl: process.env.REACT_APP_API_URL,
  msalConfig: {
    auth: {
      clientId: process.env.REACT_APP_AZURE_CLIENT_ID,
      authority: process.env.REACT_APP_AZURE_AUTHORITY,
      redirectUri: process.env.REACT_APP_REDIRECT_URI
    }
  }
};
```

---

## Security Considerations

1. **Password Storage**
   - Use `password_hash()` for standard users
   - Never store plain text passwords

2. **Token Security**
   - JWT tokens expire after 2 hours
   - Refresh tokens for extended sessions
   - HTTPS only for production

3. **Session Management**
   - Regenerate session IDs on login
   - Clear sessions on logout
   - Implement CSRF protection

4. **Rate Limiting**
   - Limit login attempts (5 per minute)
   - Implement CAPTCHA after failures

---

## User Experience Flow

### For Standard Users:
1. Enter email
2. System checks auth_type
3. Shows password field
4. Validates credentials
5. Creates session/token
6. Redirects to dashboard

### For SSO Users:
1. Enter email
2. System checks auth_type
3. Shows "Login with Microsoft" button
4. Redirects to Azure AD
5. Validates returned token
6. Creates session/token
7. Redirects to dashboard

### For "Both" Type Users:
1. Enter email
2. System shows both options
3. User chooses method
4. Follows respective flow

---

## Benefits of Dual Authentication

1. **Flexibility** - Support corporate SSO and individual accounts
2. **Security** - SSO for employees, standard for external users
3. **User Management** - Centralized control over auth methods
4. **Gradual Migration** - Move users to SSO over time
5. **Compliance** - Meet enterprise security requirements

---

## Questions to Address

1. **Default auth method for new users?**
   - Recommend: Based on email domain
   - @dentwizard.com → SSO
   - Others → Standard

2. **Password reset for standard users?**
   - Implement forgot password flow
   - Email verification required

3. **Two-factor authentication?**
   - Optional for standard users
   - Handled by Azure AD for SSO

4. **Session timeout policies?**
   - Standard: 2 hours
   - SSO: Follow Azure AD policy

---

## Immediate Next Steps

1. **Confirm database changes are acceptable**
2. **Choose JWT library for PHP** (firebase/php-jwt recommended)
3. **Set up Azure AD app registration** (if not done)
4. **Create auth API endpoints**
5. **Build login page UI**

This dual authentication system will provide flexibility while maintaining security and user experience.
