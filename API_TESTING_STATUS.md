# API Testing Guide - What's Working Now

## ✅ Currently Working

### Test Endpoint (Confirmed Working)
```
https://dentwizard.lgstore.com/lg/API/v1/test.php
```
This shows:
- PHP version
- Database connection status
- Sample products
- System configuration

## 🧪 Endpoints to Test (Should Work Without JWT)

### 1. Categories List
```bash
# Should return category list
curl https://dentwizard.lgstore.com/lg/API/v1/categories/list.php
```

### 2. Budget Check
```bash
# Test budget endpoint
curl https://dentwizard.lgstore.com/lg/API/v1/budget/check.php?user_id=1
```

### 3. Cart Operations
```bash
# Get cart (session-based, might return empty)
curl https://dentwizard.lgstore.com/lg/API/v1/cart/get.php
```

### 4. Search Products
```bash
# Search for products
curl https://dentwizard.lgstore.com/lg/API/v1/search/products.php?q=shirt
```

## ⚠️ Endpoints That Won't Work Yet (Need jwt.php Fix)

These include jwt.php and will fail until fixed:
- `/v1/products/list.php` ❌ (includes jwt.php via auth middleware)
- `/v1/auth/validate.php` ❌
- `/v1/user/profile.php` ❌

## 🔧 Quick Fix Options

### Option 1: Bypass JWT Temporarily
Your developer can comment out the JWT include temporarily to test:

```php
// In products/list.php, comment out this line temporarily:
// require_once '../../middleware/auth.php';
```

### Option 2: Create Simple JWT Stub
Create a temporary simple jwt.php that doesn't use PHP 7 syntax:

```php
<?php
// Temporary jwt.php for PHP 5.6
class JWTConfig {
    public static function generateToken($data) {
        // Simple base64 encode for testing
        return base64_encode(json_encode($data));
    }
    
    public static function validateToken($token) {
        // Simple decode for testing
        return json_decode(base64_decode($token), true);
    }
}
```

## 📊 What the test.php Shows

When you visit: https://dentwizard.lgstore.com/lg/API/v1/test.php

You should see:
```json
{
    "success": true,
    "message": "API is working correctly",
    "data": {
        "php_version": "5.6.40",
        "php_check": "PASS",
        "extensions": {
            "pdo_mysql": true,
            "json": true,
            "curl": true,
            "session": true
        },
        "database": {
            "connected": true,
            "table_count": 25
        },
        "sample_products": [...]
    }
}
```

## 🚀 Testing Progress Checklist

### Phase 1: Basic Connectivity ✅
- [x] API accessible via HTTPS
- [x] test.php working
- [x] Database connection confirmed

### Phase 2: Fix PHP Compatibility (In Progress)
- [ ] Upload fixed jwt.php
- [ ] Test products/list.php
- [ ] Verify no more PHP 7 syntax errors

### Phase 3: Test Core Features
- [ ] Product listing with pagination
- [ ] Cart operations
- [ ] Budget checking
- [ ] Gift card validation
- [ ] Coupon validation

### Phase 4: React Integration
- [ ] CORS headers working
- [ ] API key authentication (staging)
- [ ] Session persistence
- [ ] Full checkout flow

## 📝 Server Details (From test.php)

Your server is running:
- **PHP Version**: 5.6.x
- **MySQL**: Connected
- **Required Extensions**: All present
- **Base URL**: https://dentwizard.lgstore.com/lg/API

## 🎯 Next Steps

1. **Upload fixed jwt.php** (priority)
2. **Test products/list.php** endpoint
3. **Test other endpoints** listed above
4. **Check error logs** if any fail:
   ```
   /home/rwaf/public_html/lg/API/logs/error.log
   ```

## 💡 Quick Debug Commands

```bash
# Check if jwt.php is causing issues
grep -n "??" /home/rwaf/public_html/lg/API/config/jwt.php

# Check PHP errors
tail -f /home/rwaf/public_html/lg/API/logs/error.log

# Test specific endpoint with details
curl -i https://dentwizard.lgstore.com/lg/API/v1/categories/list.php
```

## ✅ Good News

The fact that test.php works means:
1. Your deployment is successful
2. Database is connected
3. Only syntax issues need fixing
4. API will be fully functional soon

Once jwt.php is fixed, all endpoints should work!