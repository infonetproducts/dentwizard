# 🚨 URGENT CORS FIX - SIMPLE SOLUTION

## The Problem
Your API at https://dentwizard.lgstore.com/lg/API/ is blocking your React app because of CORS (Cross-Origin Resource Sharing) policy.

## Quick Fix (Add to EACH PHP file)

Add this code to the **VERY TOP** of EACH PHP file in your API directory:

```php
<?php
// CORS Headers - PUT THIS AT THE VERY TOP OF EACH FILE
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// YOUR EXISTING CODE CONTINUES BELOW THIS LINE
// ... rest of your PHP code ...
```

## Files That Need This Fix

Add the code above to these files on your server:

1. `/lg/API/products/list.php`
2. `/lg/API/products/detail.php`
3. `/lg/API/categories/list.php`
4. `/lg/API/cart/get.php`
5. `/lg/API/cart/add.php`
6. `/lg/API/cart/update.php`
7. `/lg/API/cart/remove.php`
8. ALL other PHP files in the API directory

## How to Test

1. Open http://localhost:3008/test-cors.html
2. Click "Test API Direct" button
3. If you see data = CORS is fixed!
4. If you see error = Add the code above to your PHP files

## Alternative: Use Direct API URL (Temporary)

If you can't fix the server right now, change your React app to use the API directly:

1. Edit `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\react-app\.env`
2. Change to: `REACT_APP_API_URL=https://dentwizard.lgstore.com/lg/API`
3. Your app will still get CORS errors until the server is fixed

## Need Help?

The proxy setup I created should work as a temporary workaround, but the REAL solution is to add the CORS headers to your PHP files on the server.

Test page: http://localhost:3008/test-cors.html
