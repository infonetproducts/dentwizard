# 🔴 URGENT: CORS Fix Required for Dentwizard Store

## Problem Summary
The React application at `http://localhost:3005` (and other ports) cannot connect to the API at `https://dentwizard.lgstore.com/lg/API/` due to CORS (Cross-Origin Resource Sharing) blocking.

**Error:** "Access to fetch at 'https://dentwizard.lgstore.com/lg/API/...' from origin 'http://localhost:3005' has been blocked by CORS policy"

## 🚀 Quick Fix Instructions

### Option 1: Add CORS Headers to Each PHP File (FASTEST)

Add these lines to the **VERY TOP** of EVERY PHP file in `/lg/API/` directory:

```php
<?php
// CORS Headers - MUST BE AT THE VERY TOP OF THE FILE
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// YOUR EXISTING PHP CODE CONTINUES BELOW...
```

### Option 2: Use the Provided Files (RECOMMENDED)

1. Upload `cors.php` from `API_FILES_TO_UPLOAD` folder to `/lg/API/cors.php`
2. Upload `.htaccess` from `API_FILES_TO_UPLOAD` folder to `/lg/API/.htaccess`
3. Add this line to the top of each PHP file: `<?php require_once 'cors.php'; ?>`

## 📁 Files That MUST Be Modified

Add CORS headers to ALL these files:
- `/lg/API/products/list.php`
- `/lg/API/products/detail.php` 
- `/lg/API/categories/list.php`
- `/lg/API/cart/get.php`
- `/lg/API/cart/add.php`
- `/lg/API/cart/update.php`
- `/lg/API/cart/remove.php`
- `/lg/API/orders/create.php`
- `/lg/API/orders/my-orders.php`
- `/lg/API/user/profile.php`
- `/lg/API/user/addresses.php`
- ANY other PHP files in the API directory

## 🧪 How to Test

1. Upload `test-cors.php` to `/lg/API/test-cors.php`
2. Visit: https://dentwizard.lgstore.com/lg/API/test-cors.php
3. You should see a JSON response with "CORS is working correctly!"

## ⚠️ Important Notes

- **Security:** For production, change `*` to specific domains:
  ```php
  header("Access-Control-Allow-Origin: https://your-production-domain.com");
  ```
- **Order Matters:** CORS headers MUST be sent before any other output
- **No HTML/Echo:** Don't output anything before these headers
