# 🚨 CORS Issue Fix - URGENT

## Problem
The API at `https://dentwizard.lgstore.com/lg/API/` is blocking requests from your React app due to CORS policy.

## 🔧 Solutions (Choose One)

### Solution 1: Fix on Server (RECOMMENDED)
Upload these files to your API directory on the server:

1. **Upload `cors.php`** to `/lg/API/cors.php`
2. **Upload `.htaccess`** to `/lg/API/.htaccess`
3. **Add to EVERY API endpoint** at the very top:
   ```php
   <?php
   require_once 'cors.php';
   // ... rest of your code
   ```

### Solution 2: Quick Fix - Add to Each Endpoint
Add this to the TOP of each PHP file in your API:

```php
<?php
// CORS headers - add this at the very beginning of each file
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Your existing code continues here...
```

### Solution 3: Temporary Workaround - Use Proxy
While waiting for server fix, add proxy to React app:

1. **Edit `package.json`:**
```json
{
  "name": "dentwizard-store",
  "version": "1.0.0",
  "proxy": "https://dentwizard.lgstore.com",
  ...
}
```

2. **Update `.env`:**
```
REACT_APP_API_URL=/lg/API
```

3. **Restart the app**

## 📁 Files to Upload

I've created these files for you:
- `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\cors.php`
- `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\.htaccess`
- `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\test-cors.php`

## 🧪 Test CORS

1. Upload `test-cors.php` to your server
2. Visit: `https://dentwizard.lgstore.com/lg/API/test-cors.php`
3. If you see JSON response, CORS is working!

## ⚡ Quick Test in Browser Console

Run this in browser console (F12):
```javascript
fetch('https://dentwizard.lgstore.com/lg/API/test-cors.php')
  .then(r => r.json())
  .then(d => console.log('CORS Working!', d))
  .catch(e => console.log('CORS Failed', e));
```

## 🎯 Which Files Need CORS Headers?

Add to these endpoints:
- `/products/list.php`
- `/products/detail.php`
- `/categories/list.php`
- `/cart/get.php`
- `/cart/add.php`
- `/cart/update.php`
- `/cart/remove.php`
- `/orders/create.php`
- `/orders/my-orders.php`
- `/user/profile.php`
- `/user/addresses.php`

## ⚠️ Important Notes

- **For Production**: Change `*` to specific domain instead of allowing all origins
- **Server Config**: Some hosts require .htaccess in root directory
- **PHP Version**: Make sure PHP 5.6+ is being used

## 🚀 After Fixing CORS

1. Clear browser cache
2. Restart React app
3. Test API calls work

The app should then connect properly to your API!