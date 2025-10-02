# ⚠️ URGENT: CORS IS NOT WORKING

## The Problem
The CORS headers are NOT being sent from the server. The React app at localhost:3006 cannot connect to the API.

## Immediate Test
1. Upload `URGENT_cors-test-simple.php` to `/lg/API/cors-test-simple.php`
2. Visit: http://localhost:3006/debug-cors.html
3. Click "Check cors-test-simple.php"
4. If it shows ✅ = CORS is working for that file
5. If it shows ❌ = Server configuration issue

## The Fix - Add to EVERY PHP File

### ⚠️ CRITICAL: These lines MUST be at the VERY TOP of the file

```php
<?php
// NO BLANK LINES OR SPACES BEFORE <?php TAG!

// CORS Headers - ADD THESE 4 LINES
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

// REST OF YOUR EXISTING CODE CONTINUES HERE
```

## Files That MUST Be Fixed (in /lg/API/)

### High Priority (Main App Functions)
- [ ] products/list.php
- [ ] categories/list.php
- [ ] cart/get.php

### Secondary Priority
- [ ] products/detail.php
- [ ] cart/add.php
- [ ] cart/update.php
- [ ] cart/remove.php
- [ ] orders/create.php
- [ ] orders/my-orders.php
- [ ] user/profile.php
- [ ] user/addresses.php

## Common Mistakes to Avoid

### ❌ WRONG - Headers after other output
```php
<?php
echo "something";  // ❌ Output before headers
header("Access-Control-Allow-Origin: *");
```

### ❌ WRONG - Blank line before <?php
```php

<?php  // ❌ Blank line before opening tag
header("Access-Control-Allow-Origin: *");
```

### ✅ CORRECT - Headers first
```php
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

// Now your code
echo "something";
```

## Verification Steps

1. After adding headers to a file, test it:
   - Open: http://localhost:3006/debug-cors.html
   - Click button for that endpoint
   - Should show ✅ CORS CONFIGURED

2. Check in browser console:
   - Open the React app: http://localhost:3006
   - Open browser console (F12)
   - Should NOT see "blocked by CORS policy" errors

## If .htaccess Method Doesn't Work

Some servers don't process .htaccess files or don't have mod_headers enabled. 
In that case, you MUST add the PHP headers to each file manually.

## SSH Quick Fix (If Available)

If you have SSH access, you can use the auto-fix script:
```bash
cd /path/to/lg/API
php auto-fix-cors.php
```

## Need Help?

Check server error logs if you see 500 errors after adding headers.
Common issue: UTF-8 BOM in files can cause header errors.

---
**THIS IS URGENT - The app cannot function without these headers!**
