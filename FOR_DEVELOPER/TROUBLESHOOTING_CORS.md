# 🚨 CORS Still Not Working - Troubleshooting Guide

## The Problem
The headers are NOT being sent even though they were supposedly added. Here are the possible causes:

## 1. Check for UTF-8 BOM (Most Common Issue)

Some editors add an invisible BOM (Byte Order Mark) at the start of files which prevents headers from working.

### How to Fix:
- Open each PHP file in Notepad++
- Go to Encoding → Convert to UTF-8 (without BOM)
- Save the file

OR use this command if you have SSH:
```bash
# Remove BOM from all PHP files
find /path/to/lg/API -name "*.php" -exec sed -i '1s/^\xEF\xBB\xBF//' {} \;
```

## 2. Check for Output Before Headers

Make sure there is NOTHING before the headers:

### ❌ WRONG - Space before <?php
```php
 <?php
header("Access-Control-Allow-Origin: *");
```

### ❌ WRONG - Blank line before <?php
```php

<?php
header("Access-Control-Allow-Origin: *");
```

### ❌ WRONG - HTML before PHP
```html
<!DOCTYPE html>
<?php
header("Access-Control-Allow-Origin: *");
```

### ✅ CORRECT - Headers immediately after <?php
```php
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }
```

## 3. Test with a Simple File

Create a NEW file `/lg/API/test-headers.php` with EXACTLY this content:

```php
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }
echo json_encode(["status" => "CORS headers are working!"]);
?>
```

Then test it:
```bash
curl -I https://dentwizard.lgstore.com/lg/API/test-headers.php
```

Should show:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
```

## 4. Check PHP Errors

Add this temporarily to see if there are PHP errors:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }
```

## 5. Clear Everything

1. **Clear Browser Cache:**
   - Chrome: Ctrl+Shift+Delete → Clear browsing data
   - Check "Cached images and files"
   - Clear data

2. **Clear Server Cache (if using any):**
   - CloudFlare: Put in Development Mode
   - Server cache: Clear if applicable

3. **Hard Refresh:**
   - Ctrl+Shift+R on the page

## 6. Check with Command Line

Have your developer run this from the server:
```bash
php -r "
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
echo 'If you see this without errors, PHP headers work';
"
```

## 7. Alternative: Force Headers with .htaccess

If PHP headers aren't working, try forcing them with Apache:

Create/update `/lg/API/.htaccess`:
```apache
<IfModule mod_headers.c>
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, X-Session-ID"
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ - [R=200,L]
</IfModule>
```

## 8. Check File Permissions

Make sure files are readable:
```bash
chmod 644 /path/to/lg/API/*.php
chmod 644 /path/to/lg/API/*/*.php
```

## 9. Verify Headers Are Actually There

Check the actual file content:
```bash
head -n 10 /path/to/lg/API/products/list.php
```

Should show:
```php
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }
```

## 10. Nuclear Option - Replace a File Completely

Replace `/lg/API/products/list.php` with this minimal version to test:

```php
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

// Minimal response for testing
header('Content-Type: application/json');
echo json_encode([
    "success" => true,
    "products" => [],
    "message" => "CORS is working but returning empty products for test"
]);
?>
```

## Still Not Working?

If none of the above works, the issue might be:
- Web server configuration blocking headers
- CDN or proxy stripping headers
- ModSecurity or similar blocking the headers
- PHP running in CGI mode with different header handling

Ask your hosting provider about CORS support.
