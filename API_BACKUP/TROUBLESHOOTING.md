# Extended Troubleshooting Guide for PHP 5.6 API

## Table of Contents
1. [Installation Issues](#installation-issues)
2. [Database Connection Problems](#database-connection-problems)
3. [PHP 5.6 Compatibility Errors](#php-56-compatibility-errors)
4. [Authentication & SSO Issues](#authentication--sso-issues)
5. [CORS and Cross-Origin Problems](#cors-and-cross-origin-problems)
6. [API Response Errors](#api-response-errors)
7. [Performance Issues](#performance-issues)
8. [Session Management Problems](#session-management-problems)
9. [File Permission Issues](#file-permission-issues)
10. [Debugging Techniques](#debugging-techniques)

---

## Installation Issues

### Problem: Composer fails with PHP version error
```
Your requirements could not be resolved to an installable set of packages
```

**Solution:**
```bash
# Force PHP 5.6 platform
composer config platform.php 5.6.40
composer update --ignore-platform-reqs

# Or modify composer.json
"config": {
    "platform": {
        "php": "5.6.40"
    }
}
```

### Problem: "composer: command not found"
**Solution:**
```bash
# Windows - Download composer
https://getcomposer.org/Composer-Setup.exe

# Or use PHP directly
php composer.phar install

# Linux/Mac
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

### Problem: SSL certificate error during composer install
**Solution:**
```bash
# Temporary fix (not for production!)
composer config --global disable-tls true
composer config --global secure-http false

# Better solution - Update CA certificates
# Download from: https://curl.haxx.se/ca/cacert.pem
# Add to php.ini:
curl.cainfo = "C:/path/to/cacert.pem"
openssl.cafile = "C:/path/to/cacert.pem"
```

---

## Database Connection Problems

### Problem: "Connection refused" or "Can't connect to MySQL"
**Diagnosis:**
```php
<?php
// test-connection.php
$host = 'localhost';
$user = 'your_user';
$pass = 'your_pass';

// Test with mysql_connect (PHP 5.6)
$conn = @mysql_connect($host, $user, $pass);
if (!$conn) {
    echo "MySQL Error: " . mysql_error() . "\n";
}

// Test with PDO
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "PDO Success!\n";
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
}

// Test with mysqli
$mysqli = @new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    echo "MySQLi Error: " . $mysqli->connect_error . "\n";
}
?>
```

**Solutions:**
1. Check MySQL service is running:
   ```bash
   # Windows
   net start MySQL
   
   # Linux
   service mysql status
   systemctl status mysql
   ```

2. Verify credentials:
   ```bash
   mysql -u username -p
   ```

3. Check hostname:
   - Try `127.0.0.1` instead of `localhost`
   - Check if using socket connection

4. Firewall/Port issues:
   ```bash
   telnet localhost 3306
   netstat -an | grep 3306
   ```

### Problem: "Unknown database" error
**Solution:**
```sql
-- Check database exists
SHOW DATABASES;

-- Create if missing
CREATE DATABASE your_database_name;

-- Grant permissions
GRANT ALL PRIVILEGES ON your_database_name.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
```

### Problem: Character encoding issues (special characters showing as ???)
**Solution:**
```php
// In database.php
$pdo = new PDO($dsn, $user, $pass, array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
));

// For mysql_connect
mysql_set_charset('utf8', $connection);

// In SQL
ALTER DATABASE your_database CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE your_table CONVERT TO CHARACTER SET utf8;
```

---

## PHP 5.6 Compatibility Errors

### Problem: "Parse error: syntax error, unexpected ':'"
**Cause:** Return type declarations (PHP 7 feature)
```php
// Wrong for PHP 5.6
function getData(): array {

// Correct for PHP 5.6
function getData() {
```

### Problem: "Parse error: syntax error, unexpected '?'"
**Cause:** Null coalescing operator (PHP 7 feature)
```php
// Wrong for PHP 5.6
$value = $_GET['id'] ?? 'default';

// Correct for PHP 5.6
$value = isset($_GET['id']) ? $_GET['id'] : 'default';
```

### Problem: "Catchable fatal error: Argument must be of type string"
**Cause:** Type hints for scalars (PHP 7 feature)
```php
// Wrong for PHP 5.6
function setName(string $name) {

// Correct for PHP 5.6
function setName($name) {
    if (!is_string($name)) {
        throw new InvalidArgumentException('Name must be a string');
    }
```

### Problem: Anonymous classes not working
**Solution:** PHP 5.6 doesn't support anonymous classes at all
```php
// Wrong for PHP 5.6
$obj = new class {
    public function test() {}
};

// Correct for PHP 5.6
class TempClass {
    public function test() {}
}
$obj = new TempClass();
```

---

## Authentication & SSO Issues

### Problem: "Invalid token" with Auth0
**Diagnosis:**
```php
// Debug token
$token_parts = explode('.', $token);
$header = base64_decode($token_parts[0]);
$payload = base64_decode($token_parts[1]);

echo "Header: " . $header . "\n";
echo "Payload: " . $payload . "\n";

// Check expiration
$decoded = json_decode($payload, true);
if ($decoded['exp'] < time()) {
    echo "Token expired!\n";
}
```

**Solutions:**
1. Verify Auth0 configuration:
   ```php
   echo "Domain: " . getenv('AUTH0_DOMAIN') . "\n";
   echo "Client ID: " . getenv('AUTH0_CLIENT_ID') . "\n";
   ```

2. Check token format:
   - Should have 3 parts separated by dots
   - Each part should be base64 encoded

3. Validate audience and issuer:
   ```php
   if ($decoded['aud'] !== getenv('AUTH0_CLIENT_ID')) {
       echo "Audience mismatch!\n";
   }
   ```

### Problem: JWT signature verification fails
**Solution:**
```php
// Check secret key
$secret = getenv('JWT_SECRET');
if (strlen($secret) < 32) {
    echo "JWT_SECRET too short! Use at least 32 characters\n";
}

// Test signature manually
$signature = hash_hmac('sha256', 
    $token_parts[0] . '.' . $token_parts[1], 
    $secret, 
    true
);
$expected = base64_encode($signature);
```

### Problem: Session not persisting after login
**Solutions:**
1. Check session configuration:
   ```php
   // In auth endpoint
   session_start();
   echo "Session ID: " . session_id() . "\n";
   echo "Save path: " . session_save_path() . "\n";
   echo "Cookie params: ";
   print_r(session_get_cookie_params());
   ```

2. Verify session directory is writable:
   ```bash
   ls -la /var/lib/php/sessions/
   # or on Windows
   dir C:\Windows\Temp
   ```

3. Check cookie settings:
   ```php
   // Force session cookie
   ini_set('session.use_cookies', 1);
   ini_set('session.use_only_cookies', 1);
   ini_set('session.cookie_httponly', 1);
   ```

---

## CORS and Cross-Origin Problems

### Problem: "Access to fetch at ... has been blocked by CORS policy"
**Complete Solution:**
```php
// config/cors.php - Complete CORS setup
<?php
// Get origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Allowed origins
$allowed_origins = array(
    'http://localhost:3000',
    'http://localhost:3001',
    'https://your-react-app.onrender.com'
);

// Check if origin is allowed
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    // For development only!
    header("Access-Control-Allow-Origin: *");
}

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400");
    http_response_code(204);
    exit;
}

// Regular request headers
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
?>
```

### Problem: Cookies not sent with CORS requests
**Solution:**
```javascript
// React side
fetch('https://api.example.com', {
    credentials: 'include'  // Important!
});

// PHP side
header("Access-Control-Allow-Credentials: true");
// Cannot use * with credentials
header("Access-Control-Allow-Origin: https://specific-origin.com");
```

---

## API Response Errors

### Problem: Empty response or white page
**Diagnosis:**
```php
// Enable all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check for fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error['type'] === E_ERROR) {
        echo json_encode(array(
            'success' => false,
            'error' => $error['message']
        ));
    }
});
```

### Problem: "Headers already sent" error
**Common Causes:**
1. Whitespace before `<?php`
2. BOM (Byte Order Mark) in files
3. echo/print before header()

**Solution:**
```php
// Check for output
if (headers_sent($file, $line)) {
    echo "Headers sent in $file on line $line";
}

// Buffer output
ob_start();
// Your code here
ob_end_flush();
```

### Problem: JSON encoding fails
**Solution:**
```php
// Handle encoding errors
$json = json_encode($data);
if ($json === false) {
    switch (json_last_error()) {
        case JSON_ERROR_UTF8:
            echo 'UTF-8 encoding error';
            // Fix: Convert to UTF-8
            array_walk_recursive($data, function(&$item) {
                if (is_string($item)) {
                    $item = utf8_encode($item);
                }
            });
            break;
        case JSON_ERROR_INF_OR_NAN:
            echo 'Inf or NaN in data';
            break;
    }
}
```

---

## Performance Issues

### Problem: Slow API responses
**Diagnosis:**
```php
// Measure execution time
$start = microtime(true);

// Your code here

$end = microtime(true);
$time = ($end - $start) * 1000;
error_log("Execution time: {$time}ms");
```

**Solutions:**
1. Add database indexes:
   ```sql
   EXPLAIN SELECT * FROM Items WHERE CID = 1;
   CREATE INDEX idx_items_cid ON Items(CID);
   ```

2. Enable query caching:
   ```php
   $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
   ```

3. Optimize queries:
   ```php
   // Bad - N+1 problem
   foreach ($products as $product) {
       $stmt = $pdo->query("SELECT * FROM images WHERE product_id = " . $product['id']);
   }
   
   // Good - Single query
   $stmt = $pdo->query("SELECT * FROM products p 
                        LEFT JOIN images i ON p.id = i.product_id");
   ```

---

## Session Management Problems

### Problem: Session data lost between requests
**Diagnosis:**
```php
// session-test.php
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session data: ";
print_r($_SESSION);

// Set test value
$_SESSION['test'] = time();
echo "Set test value: " . $_SESSION['test'];
```

**Solutions:**
1. Check session save path:
   ```php
   $path = session_save_path();
   if (!is_writable($path)) {
       echo "Session path not writable: $path";
   }
   ```

2. Fix session configuration:
   ```php
   ini_set('session.save_path', '/tmp');
   ini_set('session.gc_maxlifetime', 3600);
   ini_set('session.cookie_lifetime', 3600);
   ```

---

## File Permission Issues

### Problem: "Permission denied" errors
**Windows Solution:**
```powershell
# Give full control to IIS_IUSRS or USERS
icacls "C:\path\to\api" /grant IIS_IUSRS:F /T
```

**Linux Solution:**
```bash
# Set proper ownership
chown -R www-data:www-data /path/to/api

# Set permissions
chmod -R 755 /path/to/api
chmod -R 777 /path/to/api/temp  # For writable directories
```

---

## Debugging Techniques

### 1. Complete Error Logging
```php
// Add to config file
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Custom error handler
set_error_handler(function($severity, $message, $file, $line) {
    $log = date('Y-m-d H:i:s') . " [$severity] $message in $file:$line\n";
    file_put_contents('api_errors.log', $log, FILE_APPEND);
});
```

### 2. Request/Response Logging
```php
// Log all API requests
$request_log = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'headers' => getallheaders(),
    'body' => file_get_contents('php://input')
);
file_put_contents('requests.log', json_encode($request_log) . "\n", FILE_APPEND);
```

### 3. Database Query Logging
```php
// Log all queries
class LoggingPDO extends PDO {
    public function query($query) {
        error_log("SQL: $query");
        return parent::query($query);
    }
}
```

### 4. Test Individual Components
```php
// Component test script
require_once 'config/database.php';
require_once 'middleware/auth.php';

echo "Testing database connection...\n";
try {
    $pdo = getPDOConnection();
    echo "✓ Database connected\n";
} catch (Exception $e) {
    echo "✗ Database failed: " . $e->getMessage() . "\n";
}

echo "\nTesting JWT creation...\n";
$token = AuthMiddleware::createToken(array(
    'user_id' => 1,
    'email' => 'test@example.com'
));
echo "✓ Token created: " . substr($token, 0, 20) . "...\n";
```

---

## Quick Fix Checklist

When nothing seems to work:

1. [ ] Clear all caches
2. [ ] Restart web server
3. [ ] Check file permissions (777 for testing)
4. [ ] Verify .env file exists and readable
5. [ ] Test with minimal code
6. [ ] Check PHP error log
7. [ ] Verify PHP version: `php -v`
8. [ ] Test database connection separately
9. [ ] Disable CORS temporarily
10. [ ] Use Postman instead of browser

---

## Emergency Fixes

### Make everything work quickly (NOT for production!):
```php
// Ultimate debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");
```

### Force PHP 5.6 compatibility:
```php
// At top of every file
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    die('PHP 5.6 or higher required');
}
```

This troubleshooting guide should help resolve 95% of issues!