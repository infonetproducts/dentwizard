# PHP 5.6 E-commerce API - Developer Implementation Guide

## Quick Start (15 Minutes)

### Step 1: Check Your PHP Version
```bash
php -v
# Should show PHP 5.6.x or higher
```

### Step 2: Navigate to API Directory
```bash
cd C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API
```

### Step 3: Install Dependencies
```bash
composer install
```
**Note:** If composer isn't installed, download from: https://getcomposer.org/download/

### Step 4: Configure Database
```bash
copy .env.example .env
notepad .env
```

Add your database credentials:
```ini
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_username
DB_PASS=your_password
JWT_SECRET=any-random-32-character-string-here
BASE_URL=https://your-server.com
```

### Step 5: Test the API
Open browser and visit:
```
http://your-server.com/API/v1/test.php
```

You should see JSON with system information and "success": true

---

## Complete Setup Instructions

### 1. Server Requirements
- **PHP Version:** 5.6 or higher (you have 5.6 ✓)
- **Required PHP Extensions:**
  - PDO MySQL
  - JSON
  - cURL
  - Session
  - OpenSSL (for JWT)

### 2. File Structure
```
API/
├── config/
│   ├── cors.php         ✓ Ready (PHP 5.6 compatible)
│   ├── database.php      ✓ Ready (PHP 5.6 compatible)
│   ├── jwt.php          ✓ Ready
│   └── .env             ← You create this from .env.example
├── middleware/
│   └── auth.php         ✓ Ready (PHP 5.6 compatible)
├── v1/
│   ├── test.php         ✓ Ready - Test this first!
│   ├── products/
│   │   ├── list.php     ✓ Ready
│   │   └── detail.php   ⚠ Needs PHP 5.6 update
│   ├── cart/
│   │   ├── add.php      ✓ Ready
│   │   └── get.php      ⚠ Needs PHP 5.6 update
│   ├── auth/
│   │   └── validate.php ⚠ NEEDS SSO IMPLEMENTATION
│   └── [other endpoints] ⚠ Need PHP 5.6 updates
├── composer.json        ✓ Ready (PHP 5.6 versions)
└── .htaccess           ✓ Ready
```

---

## Priority Tasks (In Order)

### Task 1: Database Connection (30 minutes)
1. Edit `.env` file with your database credentials
2. Run test script:
```bash
php test-database.php
```
3. Verify all tables are found

### Task 2: Test Basic Endpoints (30 minutes)
```bash
# Test system endpoint
curl http://your-server.com/API/v1/test.php

# Test products endpoint
curl http://your-server.com/API/v1/products/list.php?client_id=1

# Test categories
curl http://your-server.com/API/v1/categories/list.php
```

### Task 3: Implement SSO Authentication (2-4 hours)

**CRITICAL: The SSO validation is currently a placeholder!**

Edit `v1/auth/validate.php` and add ONE of these:

#### Option A: Auth0 Implementation
```php
<?php
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Install: composer require auth0/auth0-php:^5.0

use Auth0\SDK\Auth0;

$auth0 = new Auth0([
    'domain' => 'YOUR_DOMAIN.auth0.com',
    'client_id' => 'YOUR_CLIENT_ID',
    'client_secret' => 'YOUR_CLIENT_SECRET',
    'redirect_uri' => 'https://your-react-app.com/callback',
    'scope' => 'openid profile email'
]);

// Get token from request
$input = json_decode(file_get_contents('php://input'), true);
$sso_token = isset($input['sso_token']) ? $input['sso_token'] : null;

try {
    // Verify Auth0 token
    $userInfo = $auth0->getUser();
    
    if (!$userInfo) {
        throw new Exception('Invalid token');
    }
    
    // Get or create user in your database
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->execute([$userInfo['email']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create new user
        $stmt = $pdo->prepare("
            INSERT INTO Users (Email, Name, CreatedDate) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([
            $userInfo['email'],
            $userInfo['name']
        ]);
        $user_id = $pdo->lastInsertId();
    } else {
        $user_id = $user['UID'];
    }
    
    // Create JWT token for API use
    $jwt_token = AuthMiddleware::createToken([
        'user_id' => $user_id,
        'email' => $userInfo['email'],
        'name' => $userInfo['name'],
        'client_id' => 1 // Set appropriate client ID
    ]);
    
    echo json_encode([
        'success' => true,
        'token' => $jwt_token,
        'user' => [
            'id' => $user_id,
            'email' => $userInfo['email'],
            'name' => $userInfo['name']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Authentication failed'
    ]);
}
?>
```

#### Option B: Simple Email/Password (Temporary)
```php
<?php
// For testing without SSO provider
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? $input['email'] : null;
$password = isset($input['password']) ? $input['password'] : null;

$pdo = getPDOConnection();
$stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['Password'])) {
    $jwt_token = AuthMiddleware::createToken([
        'user_id' => $user['UID'],
        'email' => $user['Email'],
        'name' => $user['Name'],
        'client_id' => $user['CID']
    ]);
    
    echo json_encode([
        'success' => true,
        'token' => $jwt_token
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid credentials'
    ]);
}
?>
```

### Task 4: Update Remaining Endpoints for PHP 5.6 (2 hours)

For each endpoint that needs updating, apply these changes:

**PHP 5.6 Conversion Checklist:**
- [ ] Remove all type hints from function parameters
- [ ] Remove all return type declarations
- [ ] Replace `??` with `isset() ? : `
- [ ] Replace `$var = $array['key'] ?? null` with `$var = isset($array['key']) ? $array['key'] : null`
- [ ] Check array syntax (use `array()` or `[]` consistently)
- [ ] Test with `php -l filename.php` for syntax errors

**Example Conversion:**
```php
// PHP 7+ (DON'T USE)
public function getProduct(int $id): ?array {
    $name = $_GET['name'] ?? 'default';
    return $this->data[$id] ?? null;
}

// PHP 5.6 (USE THIS)
public function getProduct($id) {
    $name = isset($_GET['name']) ? $_GET['name'] : 'default';
    return isset($this->data[$id]) ? $this->data[$id] : null;
}
```

---

## Testing Guide

### 1. Basic Connectivity Test
```php
// Create test.php in API root
<?php
require_once 'config/database.php';
$pdo = getPDOConnection();
echo $pdo ? "✓ Database connected" : "✗ Database failed";
?>
```

### 2. Products Endpoint Test
```bash
# Should return product list
curl -X GET "http://your-server.com/API/v1/products/list.php?client_id=1"
```

### 3. Cart Test
```bash
# Add item to cart
curl -X POST "http://your-server.com/API/v1/cart/add.php" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":2,"size":"Large","color":"Blue"}'
```

### 4. Authentication Test
```bash
# Test auth endpoint (after SSO implementation)
curl -X POST "http://your-server.com/API/v1/auth/validate.php" \
  -H "Content-Type: application/json" \
  -d '{"sso_token":"your-test-token"}'
```

---

## Troubleshooting

### Problem: "Class not found" errors
**Solution:** Run `composer install` and check PHP version compatibility

### Problem: Database connection fails
**Solution:** 
1. Verify credentials in `.env`
2. Check if PDO MySQL extension is installed: `php -m | grep pdo`
3. Test direct connection: `mysql -u username -p database_name`

### Problem: 500 Internal Server Error
**Solution:**
1. Check PHP error log
2. Add to top of file for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Problem: CORS errors from React
**Solution:** Check `config/cors.php` and add your React URL to allowed origins

### Problem: JWT tokens not working
**Solution:** 
1. Ensure JWT_SECRET is set in `.env`
2. Check if openssl extension is enabled
3. Verify token format in request headers

---

## Security Checklist (Before Production)

- [ ] Change JWT_SECRET to strong random string (32+ characters)
- [ ] Update CORS allowed origins (remove `*`)
- [ ] Disable error display (`display_errors = 0`)
- [ ] Use HTTPS only
- [ ] Implement rate limiting
- [ ] Add request logging
- [ ] Validate all user inputs
- [ ] Use prepared statements (already done ✓)

---

## React Integration Points

Once API is working, React will connect to these endpoints:

```javascript
// React API configuration
const API_BASE = 'https://your-server.com/API/v1';

const endpoints = {
  auth: `${API_BASE}/auth/validate.php`,
  products: `${API_BASE}/products/list.php`,
  productDetail: `${API_BASE}/products/detail.php`,
  cart: `${API_BASE}/cart/get.php`,
  addToCart: `${API_BASE}/cart/add.php`,
  checkout: `${API_BASE}/checkout/submit.php`,
  orders: `${API_BASE}/orders/list.php`
};
```

---

## Contact & Support

### If You Get Stuck:
1. Check `test-environment.php` output
2. Review PHP error logs
3. Verify all files are uploaded
4. Test with simple curl commands
5. Check PHP 5.6 compatibility

### Common PHP 5.6 Issues:
- No `??` operator (use `isset()` instead)
- No type hints (remove them)
- No return types (remove them)
- Array syntax (both `array()` and `[]` work)

---

## Success Metrics

You'll know everything is working when:
- [ ] `/v1/test.php` returns success with database connected
- [ ] Products endpoint returns your product list
- [ ] Cart operations work (add/get)
- [ ] Authentication returns JWT token
- [ ] No PHP errors in error log
- [ ] React app can connect and fetch data

---

## Time Estimate

- Environment setup: 30 minutes
- Database configuration: 30 minutes  
- Testing endpoints: 1 hour
- SSO implementation: 2-4 hours
- Updating remaining endpoints: 2 hours
- **Total: 6-8 hours**

## Next Steps After API is Working

1. Deploy React app to Render
2. Configure React environment variables
3. Test React-API communication
4. Implement remaining features incrementally

---

## Quick Command Reference

```bash
# Test PHP syntax
php -l path/to/file.php

# Check installed extensions
php -m

# Start local test server
php -S localhost:8000

# Test endpoint with auth
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     http://your-server.com/API/v1/products/list.php

# View PHP configuration
php -i | grep -i pdo
```

**Remember: The API is 40% complete. Focus on getting authentication working first, then test with React!**# Additional Developer Guide Sections

## Endpoint Implementation Templates

### Template for New PHP 5.6 Endpoints
Use this template when creating new endpoints:

```php
<?php
// api/v1/[section]/[action].php
// PHP 5.6 Compatible Endpoint Template

// Required includes
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Authentication (choose one)
// Option 1: Require authentication
AuthMiddleware::validateRequest();
$user_id = $GLOBALS['auth_user']['id'];
$client_id = $GLOBALS['auth_user']['client_id'];

// Option 2: Optional authentication
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    AuthMiddleware::validateRequest();
}

// Option 3: No authentication needed
// (skip auth middleware)

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different methods
switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(array(
            'success' => false,
            'error' => 'Method not allowed'
        ));
        exit;
}

function handleGet() {
    // Get parameters (PHP 5.6 style)
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    
    // Validation
    if ($id === null) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'ID required'
        ));
        return;
    }
    
    try {
        // Database operation
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare("SELECT * FROM table WHERE id = :id");
        $stmt->execute(array('id' => $id));
        $data = $stmt->fetch();
        
        // Response
        echo json_encode(array(
            'success' => true,
            'data' => $data
        ));
    } catch (Exception $e) {
        error_log('Endpoint error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(array(
            'success' => false,
            'error' => 'Server error'
        ));
    }
}

function handlePost() {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation (PHP 5.6 style)
    if (!$input || !isset($input['required_field'])) {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Invalid input'
        ));
        return;
    }
    
    // Process...
}

function handlePut() {
    // Similar to POST
}

function handleDelete() {
    // Delete logic
}
?>
```

---

## Database Migration Scripts

### Add SSO Support to Users Table
```sql
-- backup_users_table.sql
CREATE TABLE Users_backup AS SELECT * FROM Users;

-- add_sso_columns.sql
ALTER TABLE Users 
ADD COLUMN Auth0ID VARCHAR(255) DEFAULT NULL,
ADD COLUMN OktaID VARCHAR(255) DEFAULT NULL,
ADD COLUMN AzureID VARCHAR(255) DEFAULT NULL,
ADD COLUMN ProfilePicture TEXT DEFAULT NULL,
ADD COLUMN LastLogin DATETIME DEFAULT NULL,
ADD COLUMN UserType VARCHAR(20) DEFAULT 'customer',
ADD INDEX idx_auth0 (Auth0ID),
ADD INDEX idx_okta (OktaID),
ADD INDEX idx_azure (AzureID),
ADD INDEX idx_email (Email);

-- Create UserCarts table for persistent carts
CREATE TABLE IF NOT EXISTS UserCarts (
    CartID INT AUTO_INCREMENT PRIMARY KEY,
    UID INT NOT NULL,
    CID INT NOT NULL,
    cart_data LONGTEXT,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UID) REFERENCES Users(UID),
    INDEX idx_user_cart (UID)
);

-- Create API logs table
CREATE TABLE IF NOT EXISTS api_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255),
    method VARCHAR(10),
    user_id INT,
    ip_address VARCHAR(45),
    request_data TEXT,
    response_code INT,
    response_time FLOAT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_endpoint (endpoint),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
);
```

---

## Testing Scripts

### Complete API Test Suite
Create `test-all-endpoints.php`:

```php
<?php
// test-all-endpoints.php
// Complete API testing script

$base_url = 'http://localhost/API/v1';
$test_token = null;
$results = array();

// Color codes for terminal
$green = "\033[32m";
$red = "\033[31m";
$yellow = "\033[33m";
$reset = "\033[0m";

echo "=================================\n";
echo "PHP 5.6 API Test Suite\n";
echo "=================================\n\n";

// Test 1: System Check
echo "1. Testing system endpoint... ";
$response = testEndpoint('GET', '/test.php');
if ($response && $response['success']) {
    echo $green . "PASSED" . $reset . "\n";
    $results['system'] = true;
} else {
    echo $red . "FAILED" . $reset . "\n";
    $results['system'] = false;
}

// Test 2: Products List
echo "2. Testing products list... ";
$response = testEndpoint('GET', '/products/list.php?client_id=1');
if ($response && $response['success']) {
    echo $green . "PASSED" . $reset . "\n";
    $results['products'] = true;
    $product_id = isset($response['data']['products'][0]['product_id']) 
                  ? $response['data']['products'][0]['product_id'] 
                  : null;
} else {
    echo $red . "FAILED" . $reset . "\n";
    $results['products'] = false;
}

// Test 3: Add to Cart
echo "3. Testing add to cart... ";
if (isset($product_id)) {
    $response = testEndpoint('POST', '/cart/add.php', array(
        'product_id' => $product_id,
        'quantity' => 2
    ));
    if ($response && $response['success']) {
        echo $green . "PASSED" . $reset . "\n";
        $results['cart_add'] = true;
    } else {
        echo $red . "FAILED" . $reset . "\n";
        $results['cart_add'] = false;
    }
} else {
    echo $yellow . "SKIPPED (no product)" . $reset . "\n";
    $results['cart_add'] = null;
}

// Test 4: View Cart
echo "4. Testing view cart... ";
$response = testEndpoint('GET', '/cart/get.php');
if ($response) {
    echo $green . "PASSED" . $reset . "\n";
    $results['cart_view'] = true;
} else {
    echo $red . "FAILED" . $reset . "\n";
    $results['cart_view'] = false;
}

// Test 5: Categories
echo "5. Testing categories... ";
$response = testEndpoint('GET', '/categories/list.php');
if ($response) {
    echo $green . "PASSED" . $reset . "\n";
    $results['categories'] = true;
} else {
    echo $red . "FAILED" . $reset . "\n";
    $results['categories'] = false;
}

// Test 6: Authentication (if configured)
echo "6. Testing authentication... ";
$response = testEndpoint('POST', '/auth/validate.php', array(
    'email' => 'test@example.com',
    'password' => 'test123'
));
if ($response) {
    if ($response['success']) {
        echo $green . "PASSED" . $reset . "\n";
        $test_token = $response['token'];
        $results['auth'] = true;
    } else {
        echo $yellow . "AUTH NOT CONFIGURED" . $reset . "\n";
        $results['auth'] = null;
    }
} else {
    echo $red . "FAILED" . $reset . "\n";
    $results['auth'] = false;
}

// Summary
echo "\n=================================\n";
echo "Test Results Summary:\n";
echo "=================================\n";

$passed = 0;
$failed = 0;
$skipped = 0;

foreach ($results as $test => $result) {
    if ($result === true) {
        echo $green . "✓" . $reset . " " . ucfirst($test) . "\n";
        $passed++;
    } elseif ($result === false) {
        echo $red . "✗" . $reset . " " . ucfirst($test) . "\n";
        $failed++;
    } else {
        echo $yellow . "○" . $reset . " " . ucfirst($test) . "\n";
        $skipped++;
    }
}

echo "\nTotal: $passed passed, $failed failed, $skipped skipped\n";

// Helper function
function testEndpoint($method, $endpoint, $data = null) {
    global $base_url, $test_token;
    
    $url = $base_url . $endpoint;
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = array('Content-Type: application/json');
    if ($test_token) {
        $headers[] = 'Authorization: Bearer ' . $test_token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && $method !== 'GET') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($response, true);
    }
    
    return false;
}
?>
```

---

## Performance Optimization

### Database Query Optimization
```php
// Optimize product queries with proper indexing
// Add these indexes to improve performance:

CREATE INDEX idx_items_cid_active ON Items(CID, Active);
CREATE INDEX idx_items_category ON Items(Category);
CREATE INDEX idx_items_search ON Items(item_title, ItemNumber);
CREATE INDEX idx_orders_uid_cid ON Orders(UID, CID);
CREATE INDEX idx_orderitems_orderid ON OrderItems(OrderID);

// Use EXPLAIN to analyze queries
EXPLAIN SELECT * FROM Items WHERE CID = 1 AND Active = 1;
```

### Caching Implementation (PHP 5.6 compatible)
```php
// Simple file-based cache for PHP 5.6
class SimpleCache {
    private $cache_dir = '/tmp/api_cache/';
    
    public function __construct() {
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0777, true);
        }
    }
    
    public function get($key) {
        $file = $this->cache_dir . md5($key);
        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] > time()) {
                return $data['value'];
            }
            unlink($file);
        }
        return null;
    }
    
    public function set($key, $value, $ttl = 3600) {
        $file = $this->cache_dir . md5($key);
        $data = array(
            'expires' => time() + $ttl,
            'value' => $value
        );
        file_put_contents($file, serialize($data));
    }
    
    public function clear() {
        array_map('unlink', glob($this->cache_dir . '*'));
    }
}

// Usage in endpoints
$cache = new SimpleCache();
$cache_key = 'products_' . $client_id . '_' . $page;
$products = $cache->get($cache_key);

if (!$products) {
    // Fetch from database
    $products = fetchProductsFromDB();
    $cache->set($cache_key, $products, 300); // Cache for 5 minutes
}
```

---

## Security Implementation

### Rate Limiting for PHP 5.6
```php
// Simple rate limiting implementation
class RateLimiter {
    private $cache_dir = '/tmp/rate_limit/';
    private $max_requests = 100;
    private $window = 3600; // 1 hour
    
    public function __construct() {
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0777, true);
        }
    }
    
    public function check($identifier) {
        $file = $this->cache_dir . md5($identifier);
        $requests = array();
        
        if (file_exists($file)) {
            $requests = json_decode(file_get_contents($file), true);
        }
        
        $now = time();
        // Remove old requests
        $requests = array_filter($requests, function($time) use ($now) {
            return ($now - $time) < $this->window;
        });
        
        if (count($requests) >= $this->max_requests) {
            return false; // Rate limit exceeded
        }
        
        $requests[] = $now;
        file_put_contents($file, json_encode($requests));
        return true;
    }
}

// Usage in endpoints
$limiter = new RateLimiter();
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

if (!$limiter->check($ip)) {
    http_response_code(429);
    echo json_encode(array(
        'success' => false,
        'error' => 'Too many requests'
    ));
    exit;
}
```

### Input Sanitization
```php
// Sanitization helpers for PHP 5.6
class InputSanitizer {
    
    public static function cleanString($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }
    
    public static function cleanEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    public static function cleanInt($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function cleanArray($array) {
        return array_map(array('self', 'cleanString'), $array);
    }
}

// Usage
$email = InputSanitizer::cleanEmail($_POST['email']);
if (!InputSanitizer::validateEmail($email)) {
    // Invalid email
}
```

---

## Deployment Checklist

### Pre-deployment Steps
```bash
# 1. Update environment configuration
cp .env.example .env.production
# Edit with production values

# 2. Set proper permissions
chmod 644 *.php
chmod 755 directories
chmod 600 .env

# 3. Remove debug code
find . -name "*.php" -exec grep -l "error_reporting(E_ALL)" {} \;

# 4. Test production configuration
php test-environment.php
php test-database.php
php test-all-endpoints.php

# 5. Enable opcache for PHP 5.6
# In php.ini:
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
```

### Production .htaccess
```apache
# Complete .htaccess for production
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} !=on
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
    
    # API routing
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^v1/(.*)$ v1/$1.php [L]
    
    # Security headers
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory browsing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.(env|log|sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# PHP settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 30
php_value memory_limit 128M

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/html
</IfModule>
```

---

## Monitoring & Logging

### API Request Logger
```php
// Create api_logger.php
class APILogger {
    private $log_file = 'logs/api_requests.log';
    
    public function logRequest($endpoint, $method, $user_id = null) {
        $log_entry = array(
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'method' => $method,
            'user_id' => $user_id,
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
        );
        
        $log_line = json_encode($log_entry) . "\n";
        file_put_contents($this->log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
    
    public function logError($message, $context = array()) {
        $log_entry = array(
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => 'ERROR',
            'message' => $message,
            'context' => $context
        );
        
        $log_line = json_encode($log_entry) . "\n";
        file_put_contents('logs/api_errors.log', $log_line, FILE_APPEND | LOCK_EX);
    }
}
```

---

## React Integration Examples

### Axios Configuration for React
```javascript
// api.config.js
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'https://your-server.com/API/v1';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Request interceptor for auth
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('jwt_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  error => Promise.reject(error)
);

// Response interceptor for errors
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem('jwt_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// API methods
export const apiService = {
  // Authentication
  login: (credentials) => api.post('/auth/validate.php', credentials),
  
  // Products
  getProducts: (params) => api.get('/products/list.php', { params }),
  getProduct: (id) => api.get(`/products/detail.php?id=${id}`),
  
  // Cart
  addToCart: (item) => api.post('/cart/add.php', item),
  getCart: () => api.get('/cart/get.php'),
  
  // Checkout
  checkout: (order) => api.post('/checkout/submit.php', order),
  
  // Orders
  getOrders: () => api.get('/orders/list.php')
};

export default api;
```

---

This completes all the additional sections for your developer guide!