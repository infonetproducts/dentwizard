# Implementation Guide - CORS Fix for Dentwizard API

## Overview
The React application cannot communicate with the API due to browser CORS (Cross-Origin Resource Sharing) policy. This fix enables the API to accept requests from the React application running on localhost or any other domain.

## Step-by-Step Implementation

### Step 1: Upload Files to Server

Upload these files from `API_FILES_TO_UPLOAD` folder to your server:

1. **cors.php** → Upload to `/lg/API/cors.php`
2. **.htaccess** → Upload to `/lg/API/.htaccess`
3. **test-cors.php** → Upload to `/lg/API/test-cors.php`

### Step 2: Modify Existing API Files

For EACH PHP file in `/lg/API/` directory, add ONE of these options:

#### Option A: Include cors.php (Recommended)
Add this line at the VERY TOP of each PHP file:
```php
<?php
require_once 'cors.php';
// Rest of your existing code...
```

#### Option B: Add Headers Directly
Add these lines at the VERY TOP of each PHP file:
```php
<?php
// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// Rest of your existing code...
```

### Step 3: Files That MUST Be Modified

Update ALL these files with CORS headers:

- [ ] `/lg/API/products/list.php`
- [ ] `/lg/API/products/detail.php`
- [ ] `/lg/API/categories/list.php`
- [ ] `/lg/API/cart/get.php`
- [ ] `/lg/API/cart/add.php`
- [ ] `/lg/API/cart/update.php`
- [ ] `/lg/API/cart/remove.php`
- [ ] `/lg/API/orders/create.php`
- [ ] `/lg/API/orders/my-orders.php`
- [ ] `/lg/API/user/profile.php`
- [ ] `/lg/API/user/addresses.php`
- [ ] Any other PHP files in the API directory

### Step 4: Test Your Implementation

1. Visit: https://dentwizard.lgstore.com/lg/API/test-cors.php
   - You should see a JSON response with "CORS is working correctly!"

2. Open `test-cors-locally.html` in your browser
   - Click each button to test different endpoints
   - Green = Working, Red = Needs fixing

3. Test from React app at http://localhost:3005
   - The app should now load products and categories

## Common Issues & Solutions

### Issue: Still getting CORS errors
- **Solution**: Make sure headers are at the VERY TOP of PHP files
- No output (echo, print, HTML) before headers
- Check for UTF-8 BOM in files

### Issue: .htaccess not working
- **Solution**: Your server may not have mod_headers enabled
- Use Option A or B instead (PHP headers)

### Issue: 500 Internal Server Error
- **Solution**: Check PHP syntax in modified files
- Verify cors.php file path is correct
- Check error logs on server

## Production Considerations

### 1. Security - Update Allowed Origins
Instead of `*`, specify exact domains:

```php
$allowed_origins = [
    'https://www.dentwizard.com',
    'https://dentwizard.com'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Block unauthorized origins
    http_response_code(403);
    exit('Unauthorized origin');
}
```

### 2. Remove Debug Information
- Set `display_errors` to `off` in .htaccess
- Remove test-cors.php from production

### 3. SSL/HTTPS
- Ensure all API calls use HTTPS in production
- Update React app to use production API URL

## Verification Checklist

- [ ] cors.php uploaded to /lg/API/
- [ ] .htaccess uploaded to /lg/API/
- [ ] test-cors.php uploaded and working
- [ ] All API endpoints modified with CORS headers
- [ ] Tested from React application
- [ ] No CORS errors in browser console

## Need Help?

If you encounter issues:
1. Check browser console for specific error messages
2. Verify file permissions on server (644 for files, 755 for directories)
3. Check PHP error logs on server
4. Test with test-cors.php first

## Files in This Package

```
FOR_DEVELOPER/
├── README_URGENT_CORS_FIX.md (Quick instructions)
├── IMPLEMENTATION_GUIDE.md (This file - detailed guide)
├── test-cors-locally.html (Browser test tool)
└── API_FILES_TO_UPLOAD/
    ├── cors.php (CORS configuration)
    ├── .htaccess (Apache configuration)