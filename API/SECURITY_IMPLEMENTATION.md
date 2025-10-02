# API Security Implementation Guide

## Quick Setup for Testing

Your API now has flexible security that allows you to work locally while keeping production secure.

## Environment Configuration

### 1. Local Development (.env.local)
```bash
# Most relaxed - for your local testing
ENVIRONMENT=development
API_KEY=test_key_12345
BYPASS_AUTH=true  # Skip all auth for testing
FRONTEND_URL=http://localhost:3000
```

### 2. Render Testing (.env.staging)
```bash
# Moderate security - API key required
ENVIRONMENT=staging
API_KEY=staging_key_xyz789abc
BYPASS_AUTH=false
FRONTEND_URL=https://your-app.onrender.com
```

### 3. Production (.env.production)
```bash
# Full security - SSO required
ENVIRONMENT=production
API_KEY=prod_key_super_secret_change_this
BYPASS_AUTH=false
FRONTEND_URL=https://apparel.dentwizard.com
```

## How to Use in Your API Endpoints

### Simple Implementation
Add this to the top of any endpoint that needs protection:

```php
<?php
// At the top of any endpoint file (e.g., products/list.php)
require_once '../../config/security.php';

// This automatically handles security based on environment
$user = validateRequest();

// Rest of your endpoint code...
```

### React App Configuration

#### Development Mode
```javascript
// No authentication needed for local development
const API_CONFIG = {
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json'
    // No API key needed locally
  }
};
```

#### Staging/Render Mode
```javascript
// Use API key for Render deployment
const API_CONFIG = {
  baseURL: process.env.REACT_APP_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'X-API-Key': process.env.REACT_APP_API_KEY  // Add to Render env vars
  }
};
```

#### Production Mode
```javascript
// Use full authentication
const API_CONFIG = {
  baseURL: process.env.REACT_APP_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${getAuthToken()}`  // From SSO
  }
};
```

## Testing Different Security Modes

### 1. Test Locally (No Security)
```bash
# In your API folder, create .env file:
echo "ENVIRONMENT=development" > .env
echo "BYPASS_AUTH=true" >> .env

# Test with curl (works without any auth)
curl http://localhost:8000/api/v1/products/list.php
```

### 2. Test with API Key (Staging)
```bash
# Set environment to staging
echo "ENVIRONMENT=staging" > .env
echo "API_KEY=test123" >> .env

# Test with API key
curl -H "X-API-Key: test123" http://localhost:8000/api/v1/products/list.php

# Without key (should fail)
curl http://localhost:8000/api/v1/products/list.php
# Returns: {"error":"Authentication required"}
```

### 3. Test with Origin Validation
```bash
# The API automatically allows localhost:3000
curl -H "Origin: http://localhost:3000" http://localhost:8000/api/v1/products/list.php
```

## Security Headers Explained

The API now returns security headers to help you debug:

```
X-Security-Mode: BYPASSED-DEVELOPMENT-ONLY  # Auth bypassed (dev only)
X-Security-Mode: ORIGIN-VERIFIED            # Trusted origin detected
X-Security-Mode: API-KEY-VALID              # Valid API key provided
X-Security-Mode: JWT-AUTHENTICATED          # JWT token valid
X-Security-Mode: SSO-AUTHENTICATED          # SSO session active
X-Security-Mode: LOCALHOST-ALLOWED          # Local development
```

## Which Endpoints Need Protection?

### Always Protect (High Security)
```php
// These should always require authentication
/v1/checkout/*       // Payment processing
/v1/giftcard/*       // Gift card operations
/v1/user/profile.php // User data
/v1/orders/*         // Order history
/v1/budget/*         // Budget information
```

### Optionally Protect (Medium Security)
```php
// Can be public or require API key
/v1/cart/*           // Cart operations
/v1/search/*         // Search (maybe rate limit)
```

### Public (Low Security)
```php
// These can be public but rate limited
/v1/products/list.php    // Product catalog
/v1/categories/list.php  // Categories
/v1/store/info.php       // Store information
```

## Progressive Security Implementation

### Phase 1: Current (Testing)
- ✅ Use `BYPASS_AUTH=true` for easy testing
- ✅ All endpoints accessible locally
- ✅ Focus on functionality

### Phase 2: Render Deployment (Next Week)
- 🔧 Switch to API key authentication
- 🔧 Set `BYPASS_AUTH=false`
- 🔧 Add API key to Render environment

### Phase 3: SSO Integration (After Azure AD Setup)
- 🔐 Remove bypass mode
- 🔐 Require SSO tokens
- 🔐 Full production security

## Environment Variables for Render

Add these to your Render dashboard:

```bash
# For React App (in Render dashboard)
REACT_APP_API_URL=https://your-php-server.com/api/v1
REACT_APP_API_KEY=your_staging_api_key_here

# For PHP API (.env on your PHP server)
ENVIRONMENT=staging
API_KEY=your_staging_api_key_here  # Same as above
BYPASS_AUTH=false
```

## Quick Troubleshooting

### "Authentication required" Error
```javascript
// Check you're sending the API key
console.log('Headers:', API_CONFIG.headers);
// Should show: { 'X-API-Key': 'your_key' }
```

### CORS Error
```javascript
// Check origin is in allowed list in security.php
$allowed_origins = [
    'http://localhost:3000',
    'https://your-app.onrender.com',  // Add your Render URL
];
```

### "Invalid API key" Error
```bash
# Check API key matches in both places:
# 1. PHP API .env file
grep API_KEY .env

# 2. React app environment
echo $REACT_APP_API_KEY
```

## Testing Checklist

### Local Development ✅
- [ ] Can access API without authentication
- [ ] No CORS errors from localhost:3000
- [ ] Security header shows: `BYPASSED-DEVELOPMENT-ONLY`

### Render Staging 🔧
- [ ] API key required for access
- [ ] Render domain in allowed origins
- [ ] Rate limiting active
- [ ] Security header shows: `API-KEY-VALID`

### Production Ready 🔐
- [ ] SSO authentication required
- [ ] HTTPS enforced
- [ ] Rate limiting active
- [ ] Bot protection enabled
- [ ] Security headers present

## Security Tips

1. **Never commit .env files** - Add to .gitignore
2. **Use different API keys** for dev/staging/production
3. **Rotate API keys** regularly (monthly)
4. **Monitor security.log** for suspicious activity
5. **Test each security level** before deploying

## Example: Update an Endpoint

Here's how to add security to your existing endpoints:

```php
<?php
// v1/products/list.php

// OLD (no security)
require_once '../../config/database.php';
$products = $pdo->query("SELECT * FROM Items");
echo json_encode($products->fetchAll());

// NEW (with security)
require_once '../../config/security.php';  // Add this
require_once '../../config/database.php';

validateRequest();  // Add this - handles all security

$products = $pdo->query("SELECT * FROM Items");
echo json_encode(['success' => true, 'data' => $products->fetchAll()]);
?>
```

That's it! The security system handles everything else automatically based on your environment.

---

## Summary

Your API now has:
- **Development Mode**: No auth needed (for testing)
- **Staging Mode**: API key required (for Render)  
- **Production Mode**: Full SSO authentication

You control the security level with just one environment variable: `ENVIRONMENT`

Start with `BYPASS_AUTH=true` for easy testing, then progressively add security as you move toward production.