# Token-Based Authentication Implementation

## 🎯 Why Token-Based Auth Solves Your Problem

Instead of relying on PHP sessions (which don't work across domains), we now use tokens:
- React app on `dentwizardapparel.com` 
- API on `dentwizard.lgstore.com`
- Token works across both domains!

## 📦 FILES TO UPLOAD TO YOUR SERVER

### 1. Authentication (to /lg/API/v1/auth/):
- **login-token.php** - New login that returns tokens
  - Works for ALL users
  - Generates a token on successful login
  - No hardcoded users!

### 2. User Profile (to /lg/API/v1/user/):
- **profile.php** - Updated to accept tokens
  - Validates token from header
  - Returns user data based on token
  - Works for any authenticated user

### 3. Orders (to /lg/API/v1/orders/):
- **my-orders.php** - Updated to accept tokens
  - Validates token from header
  - Returns orders for the user in the token
  - No hardcoding!

## 🔄 How It Works

### Login Flow:
1. User enters email/password
2. login-token.php validates credentials
3. Returns a token like: `base64(user_id:timestamp:random)`
4. React stores token in localStorage
5. Token sent with every API request

### API Request Flow:
1. React sends request with header: `X-Auth-Token: [token]`
2. API decodes token to get user_id
3. API returns data for that specific user
4. Works across any domain!

## ✅ What This Fixes:
- **Cross-domain authentication** - Works between any domains
- **No more hardcoding** - Works for ALL users
- **No session issues** - Doesn't rely on PHP sessions
- **Scalable** - Works on Render, AWS, anywhere!

## 🧪 Testing After Upload:

### Test with Jamie:
```
Email: jkrugger@infonetproducts.com
Password: password
```

### Test with any other user:
```
Email: [any user email]
Password: [their actual password]
```

## 🔐 Token Format:
```
Token: base64(user_id:timestamp:random)
Example: "MjAyOTY6MTcwMTIzNDU2Nzg6YWJjZGVmMTIzNDU2Nzg5MA=="
Decodes to: "20296:1701234567:abcdef1234567890"
```

## 📋 React Changes (Already Done):
- ✅ api.js sends token with all requests
- ✅ LoginPage uses login-token.php
- ✅ Token stored in localStorage
- ✅ Works across domains

## 🚀 Next Steps:
1. Upload the 3 PHP files
2. Test login with Jamie
3. Test login with another user
4. Deploy to Render - will work perfectly!

## 🔒 Security Notes:
- Tokens expire after 24 hours
- In production, consider using JWT tokens
- Add token storage in database for validation
- Use HTTPS in production

## 💡 Benefits:
- Works on ANY hosting (Render, AWS, etc.)
- No domain restrictions
- Supports multiple users
- No hardcoded data
- Industry-standard approach
