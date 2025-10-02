# 🔒 Server-Level CORS Blocking - Common Causes

## YES - This Could Be a Server-Level Block!

Many hosting providers and server configurations can block or override CORS headers. Here are the most common culprits:

## 1. CloudFlare or CDN Stripping Headers

**Symptoms:** Headers work on server but not through CDN
**Check:** Is the site using CloudFlare, Fastly, or another CDN?

**Solution:**
- CloudFlare: Put in "Development Mode" temporarily
- CloudFlare: Add Page Rule to bypass cache for /lg/API/*
- CloudFlare: Check "Respect Existing Headers" in settings

## 2. ModSecurity / Web Application Firewall (WAF)

**Symptoms:** Headers blocked as "suspicious"
**Common on:** Shared hosting, managed hosting

**Solution:**
```bash
# Add to .htaccess
<IfModule mod_security.c>
    SecRuleEngine Off
</IfModule>
```

Or whitelist specific rules:
```bash
SecRuleRemoveById 960015
SecRuleRemoveById 960032
```

## 3. Apache mod_headers Not Enabled

**Check if enabled:**
```bash
apachectl -M | grep headers
# or
apache2ctl -M | grep headers
```

**Enable it:**
```bash
# Ubuntu/Debian
sudo a2enmod headers
sudo service apache2 restart

# CentOS/RHEL
# Add to httpd.conf: LoadModule headers_module modules/mod_headers.so
sudo systemctl restart httpd
```

## 4. Nginx Proxy Stripping Headers

If Apache is behind Nginx:

**Add to Nginx config:**
```nginx
location /lg/API/ {
    proxy_pass http://backend;
    
    # Pass CORS headers
    proxy_pass_header Access-Control-Allow-Origin;
    proxy_pass_header Access-Control-Allow-Methods;
    proxy_pass_header Access-Control-Allow-Headers;
    
    # Or set them directly
    add_header Access-Control-Allow-Origin * always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, X-Session-ID" always;
    
    # Handle OPTIONS
    if ($request_method = 'OPTIONS') {
        return 204;
    }
}
```

## 5. PHP Running in CGI/FastCGI Mode

**Issue:** Headers might not work the same way
**Check:** Create phpinfo.php
```php
<?php phpinfo(); ?>
```
Look for: Server API = CGI/FastCGI

**Solution for CGI mode:**
```php
<?php
// For CGI mode, try this format:
header("Status: 200");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Status: 200");
    exit();
}
?>
```

## 6. Shared Hosting Restrictions

Many shared hosts block custom headers for "security"

**Common problematic hosts:**
- GoDaddy (blocks on basic plans)
- HostGator (may need to request enabling)
- Bluehost (check security settings)

**Solutions:**
- Contact support to enable CORS
- Upgrade to VPS/dedicated hosting
- Use a CORS proxy service

## 7. .htaccess Override Disabled

**Check if .htaccess works at all:**
```apache
# Add to .htaccess
RewriteEngine On
RewriteRule ^test-htaccess$ /test-success.txt [L]
```

If that doesn't work, .htaccess is disabled.

**Fix in Apache config:**
```apache
<Directory /path/to/lg/API>
    AllowOverride All
</Directory>
```

## 8. Suhosin Security Extension

**Check if installed:**
```bash
php -m | grep suhosin
```

**If present, add to php.ini:**
```ini
suhosin.simulation = On
; or whitelist the headers
suhosin.cookie.plainlist = Access-Control-Allow-Origin
```

## 9. Server-Level Output Buffering

**Check php.ini:**
```ini
output_buffering = Off
; or
output_buffering = 0
```

## 10. Hosting Control Panel Interference

**cPanel/Plesk/DirectAdmin might have:**
- Security policies blocking headers
- Mod_security rules
- Custom Apache configurations

**Check:**
- cPanel → Security → ModSecurity™
- Plesk → Web Application Firewall
- DirectAdmin → Custom HTTPD Configurations

## DIAGNOSTIC COMMANDS FOR YOUR DEVELOPER

Run these to identify the issue:

```bash
# 1. Check if headers module is loaded
apachectl -M | grep headers

# 2. Check PHP mode
php -i | grep "Server API"

# 3. Test headers directly with PHP CLI
php -r "header('Access-Control-Allow-Origin: *'); echo 'test';"

# 4. Check for security modules
php -m | grep -E "suhosin|security"

# 5. Check Apache error log
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/httpd/error_log

# 6. Test with curl including headers
curl -v -X OPTIONS https://dentwizard.lgstore.com/lg/API/products/list.php \
  -H "Origin: http://localhost:3009" \
  -H "Access-Control-Request-Method: GET" 2>&1 | grep -i access-control

# 7. Check if behind a proxy
curl -I https://dentwizard.lgstore.com/lg/API/products/list.php | grep -i "server\|x-powered-by"
```

## NUCLEAR OPTION - CORS Proxy

If server won't allow CORS at all, use a proxy endpoint:

Create `/lg/API/proxy.php`:
```php
<?php
// This file CAN send CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Get the requested endpoint
$endpoint = $_GET['endpoint'] ?? '';
$allowed = ['products/list', 'categories/list', 'cart/get'];

if (!in_array($endpoint, $allowed)) {
    http_response_code(403);
    exit('Invalid endpoint');
}

// Include the actual file
$_GET = array_diff_key($_GET, ['endpoint' => '']);
include __DIR__ . '/' . $endpoint . '.php';
?>
```

Then use: `https://dentwizard.lgstore.com/lg/API/proxy.php?endpoint=products/list`

## TELL YOUR DEVELOPER

> "The CORS headers are being blocked at the server level. Please run the diagnostic commands in URGENT_CORS_NOT_SENT.md section 'Server-Level CORS Blocking'. Check if:
> 1. mod_headers is enabled
> 2. CloudFlare/CDN is stripping headers  
> 3. ModSecurity/WAF is blocking
> 4. PHP is running in CGI mode
> 5. The hosting provider has restrictions
> 
> If none of those work, we may need to use the CORS proxy workaround or contact the hosting provider."

This is definitely solvable, but we need to identify what's blocking it at the server level!
