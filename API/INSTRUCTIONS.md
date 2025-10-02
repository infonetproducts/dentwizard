# API Implementation Instructions for Developer

## Overview
This document provides complete step-by-step instructions for implementing the DentWizard E-commerce API. This API will serve as the backend for a new React frontend application while maintaining compatibility with the existing PHP system.

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Initial Setup](#initial-setup)
3. [Database Configuration](#database-configuration)
4. [Environment Configuration](#environment-configuration)
5. [SSO Integration](#sso-integration)
6. [Testing Each Endpoint](#testing-each-endpoint)
7. [Deployment Steps](#deployment-steps)
8. [Troubleshooting](#troubleshooting)
9. [Security Checklist](#security-checklist)
10. [Post-Deployment Tasks](#post-deployment-tasks)

---

## Prerequisites

### Required Software
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (PHP dependency manager)
- Apache or Nginx web server
- SSL certificate (for production)

### Required PHP Extensions
- PDO
- PDO_MySQL
- JSON
- Session
- OpenSSL
- cURL (for SSO validation)

### Verify PHP Extensions
```bash
php -m
```

---

## Initial Setup

### Step 1: Position the API Files
1. Upload the entire `API` folder to your web server
2. Place it at the same level as the `lg_files` folder:
```
/var/www/html/  (or your web root)
├── lg_files/   (existing shop files)
├── API/        (new API folder)
└── ...
```

### Step 2: Install Dependencies
```bash
cd /path/to/API
composer install
```

If Composer is not installed:
```bash
# Download Composer
curl -sS https://getcomposer.org/installer | php

# Install dependencies
php composer.phar install
```

### Step 3: Set Permissions
```bash
# Make sure the web server can read all files
chmod -R 755 /path/to/API

# If using Apache, ensure .htaccess is readable
chmod 644 /path/to/API/.htaccess
```

---

## Database Configuration

### Step 1: Locate Database Credentials
Find your existing database credentials in:
- `lg_files/include/db.php`
- Or your existing configuration files

### Step 2: Create .env File
```bash
cd /path/to/API
cp .env.example .env
```

### Step 3: Edit .env File
```bash
nano .env  # or use your preferred editor
```

Update these values:
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=your_actual_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password

# JWT Configuration (IMPORTANT: Generate a strong secret key!)
JWT_SECRET_KEY=generate_a_random_32_character_string_here
JWT_EXPIRY=3600

# Environment
ENVIRONMENT=development  # Change to 'production' when ready

# Frontend URL (for CORS) - Update when React app is deployed
FRONTEND_URL=https://your-react-app.onrender.com

# Base URL for images - Your current PHP site URL
BASE_URL=https://your-current-php-site.com

# Upload paths (should match your existing setup)
UPLOAD_PATH=/uploads/
PRODUCTS_PATH=/pdf/
```

### Step 4: Generate JWT Secret Key
```bash
# Generate a secure random key
openssl rand -base64 32
```
Copy the output and use it as your JWT_SECRET_KEY in .env

---

## Environment Configuration

### Step 1: Configure Apache (if using Apache)
Ensure your Apache configuration allows .htaccess overrides:

```apache
<Directory /path/to/API>
    AllowOverride All
    Require all granted
</Directory>
```

### Step 2: Configure Nginx (if using Nginx)
Add this to your Nginx server block:

```nginx
location /API {
    try_files $uri $uri/ /API/v1/$uri.php?$query_string;
    
    # CORS headers
    add_header Access-Control-Allow-Origin $http_origin always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Content-Type, Authorization, X-Client-ID" always;
    
    if ($request_method = 'OPTIONS') {
        return 204;
    }
}
```

### Step 3: Update CORS Settings
Edit `API/config/cors.php` to add your specific domains:

```php
$allowed_origins = [
    'http://localhost:3000',          // React development
    'https://your-react-app.onrender.com',  // React production
    'https://your-domain.com'         // Any other domains
];
```

---

## SSO Integration

### IMPORTANT: The current SSO validation is a placeholder. You must implement actual validation.

### Option A: Auth0 Integration

Edit `API/v1/auth/validate.php` and replace the placeholder with:

```php
// At the top of the file
require_once '../../vendor/autoload.php';
use Auth0\SDK\Auth0;

// In the validation section
$auth0 = new Auth0([
    'domain' => $_ENV['AUTH0_DOMAIN'],
    'clientId' => $_ENV['AUTH0_CLIENT_ID'],
    'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
    'cookieSecret' => $_ENV['JWT_SECRET_KEY']
]);

try {
    $userInfo = $auth0->getUser();
    if (!$userInfo) {
        throw new Exception('Invalid token');
    }
    
    $sso_valid = true;
    $sso_user_email = $userInfo['email'];
    $sso_user_name = $userInfo['name'];
} catch (Exception $e) {
    $sso_valid = false;
}
```

Then install Auth0 SDK:
```bash
composer require auth0/auth0-php
```

### Option B: Okta Integration

```php
// Install Okta JWT Verifier
composer require okta/jwt-verifier
```

Then update validate.php:
```php
use Okta\JwtVerifier\JwtVerifierBuilder;

$jwtVerifier = (new JwtVerifierBuilder())
    ->setIssuer($_ENV['OKTA_ISSUER'])
    ->setAudience('api://default')
    ->setClientId($_ENV['OKTA_CLIENT_ID'])
    ->build();

try {
    $jwt = $jwtVerifier->verify($sso_token);
    $sso_valid = true;
    $sso_user_email = $jwt->claims['email'];
    $sso_user_name = $jwt->claims['name'];
} catch (Exception $e) {
    $sso_valid = false;
}
```

### Option C: Azure AD Integration

```bash
composer require thenetworg/oauth2-azure
```

Update validate.php accordingly with Azure AD validation logic.

---

## Testing Each Endpoint

### Step 1: Test API Health
```bash
curl http://your-server.com/API/v1/test.php
```

Expected response:
```json
{
    "success": true,
    "message": "API is working",
    "timestamp": "2024-01-15T10:30:00+00:00",
    "php_version": "7.4.33"
}
```

### Step 2: Get Test JWT Token
For testing, create a temporary test token endpoint `API/v1/auth/test-token.php`:

```php
<?php
require_once '../../config/cors.php';
require_once '../../config/jwt.php';

// REMOVE THIS FILE IN PRODUCTION!
$test_user = [
    'id' => 1,  // Use a real user ID from your database
    'email' => 'test@example.com',
    'client_id' => 56,  // Use your actual client ID
    'roles' => ['user']
];

$token = JWTManager::generateToken($test_user);

echo json_encode([
    'success' => true,
    'token' => $token,
    'note' => 'This is a test token. Remove this endpoint in production!'
]);
?>
```

Get the test token:
```bash
curl http://your-server.com/API/v1/auth/test-token.php
```

### Step 3: Test Products Endpoint
```bash
# Set your test token
TOKEN="your_jwt_token_here"

# Test products list
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/products/list.php

# Test single product
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/products/detail.php?id=41839
```

### Step 4: Test Cart Operations
```bash
# Add to cart
curl -X POST \
     -H "Authorization: Bearer $TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"product_id":41839,"quantity":2}' \
     http://your-server.com/API/v1/cart/add.php

# Get cart
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/cart/get.php
```

### Step 5: Test Categories
```bash
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/categories/list.php
```

### Step 6: Test Shop Config
```bash
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/shop/config.php
```

---

## Deployment Steps

### Step 1: Pre-Deployment Checklist
- [ ] All endpoints tested successfully
- [ ] SSO integration implemented
- [ ] .env file configured with production values
- [ ] JWT secret key is strong and unique
- [ ] CORS origins updated for production
- [ ] SSL certificate installed

### Step 2: Switch to Production Mode
In `.env`:
```env
ENVIRONMENT=production
```

### Step 3: Remove Test Files
```bash
# Remove test token endpoint if created
rm API/v1/auth/test-token.php
```

### Step 4: Enable Caching (Optional)
For better performance, enable OPcache in php.ini:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Step 5: Set Up Logging
Create log directory:
```bash
mkdir /path/to/API/logs
chmod 755 /path/to/API/logs
```

Add to `.env`:
```env
LOG_PATH=/path/to/API/logs/
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. 500 Internal Server Error
- Check PHP error logs: `tail -f /var/log/apache2/error.log`
- Verify database connection in .env
- Check file permissions

#### 2. CORS Errors
- Verify origins in `config/cors.php`
- Check that Apache/Nginx allows headers
- Ensure OPTIONS requests return 200

#### 3. Authentication Fails
- Verify JWT_SECRET_KEY is set correctly
- Check token expiry time
- Ensure session_start() is called

#### 4. Database Connection Failed
- Verify credentials in .env
- Check MySQL is running: `systemctl status mysql`
- Test connection: `mysql -u username -p database_name`

#### 5. Composer Errors
```bash
# Clear Composer cache
composer clear-cache

# Update dependencies
composer update

# Regenerate autoload
composer dump-autoload
```

#### 6. Images Not Loading
- Verify BASE_URL in .env matches your PHP site
- Check image paths in database
- Ensure images directory is accessible

### Debug Mode
For detailed error messages during testing, add to any endpoint:
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

---

## Security Checklist

### Before Going Live
- [ ] **Change JWT secret key** from default
- [ ] **Implement real SSO validation** (not placeholder)
- [ ] **Use HTTPS** everywhere
- [ ] **Remove test endpoints**
- [ ] **Set ENVIRONMENT=production**
- [ ] **Restrict CORS origins** to specific domains
- [ ] **Enable rate limiting** (Apache mod_ratelimit or Nginx limit_req)
- [ ] **Hide PHP version**: Add to php.ini: `expose_php = Off`
- [ ] **Set secure headers** in .htaccess
- [ ] **Validate all inputs** (already implemented)
- [ ] **Use prepared statements** (already implemented)
- [ ] **Log suspicious activity**
- [ ] **Regular security updates**: `composer update`

### Rate Limiting Example (Apache)
Add to .htaccess:
```apache
<IfModule mod_ratelimit.c>
    SetOutputFilter RATE_LIMIT
    SetEnv rate-limit 100
</IfModule>
```

### Rate Limiting Example (Nginx)
```nginx
http {
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    
    server {
        location /API {
            limit_req zone=api burst=20 nodelay;
        }
    }
}
```

---

## Post-Deployment Tasks

### 1. Monitor Performance
Set up monitoring for:
- API response times
- Error rates
- Database query performance
- Server resource usage

### 2. Set Up Backups
- Database backups (daily)
- Code backups (on each deployment)
- Configuration backups

### 3. Create API Documentation
Document any custom endpoints or modifications for future developers.

### 4. Load Testing
Test API under load:
```bash
# Install Apache Bench
apt-get install apache2-utils

# Test with 100 requests, 10 concurrent
ab -n 100 -c 10 -H "Authorization: Bearer $TOKEN" http://your-server.com/API/v1/products/list.php
```

### 5. Set Up Alerts
Configure alerts for:
- API downtime
- High error rates
- Slow response times
- Failed authentication attempts

---

## Additional Endpoints to Implement

Once the basic API is working, implement these additional endpoints:

### Cart Management
- `PUT /v1/cart/update.php` - Update cart item quantity
- `DELETE /v1/cart/remove.php` - Remove item from cart
- `POST /v1/cart/clear.php` - Clear entire cart

### User Management  
- `PUT /v1/user/update.php` - Update user profile
- `GET /v1/user/addresses.php` - Get saved addresses
- `POST /v1/user/addresses.php` - Add new address
- `DELETE /v1/user/addresses/{id}` - Delete address

### Orders
- `GET /v1/orders/detail.php?id={order_id}` - Get order details
- `GET /v1/orders/track.php?id={order_id}` - Track order
- `POST /v1/orders/reorder.php` - Reorder previous order

### Custom Orders
- `POST /v1/custom-order/submit.php` - Submit custom order
- `GET /v1/custom-order/check.php` - Check if item supports custom

### File Upload
- `POST /v1/upload/file.php` - Upload files for custom orders

---

## Contact & Support

### During Implementation
If you encounter issues:

1. **Check logs first**:
   - PHP error log
   - Apache/Nginx error log
   - API logs (if implemented)

2. **Test incrementally**:
   - Test each endpoint individually
   - Verify authentication works
   - Check database queries

3. **Document any issues** with:
   - Exact error message
   - Endpoint being tested
   - Request being sent
   - Response received

### Required Information for React Developer
Once API is working, provide the React developer with:

1. **API Base URL**: `https://your-server.com/API/v1`
2. **Test JWT Token**: For development
3. **Client ID**: Your specific CID
4. **SSO Configuration**: Provider details
5. **Any Custom Endpoints**: Document any modifications

---

## Quick Reference

### Test Commands Cheat Sheet
```bash
# Set token variable
TOKEN="your_jwt_token"

# Test API health
curl http://your-server.com/API/v1/test.php

# Get products
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/products/list.php

# Get single product
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/products/detail.php?id=123

# Add to cart
curl -X POST \
     -H "Authorization: Bearer $TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"product_id":123,"quantity":1}' \
     http://your-server.com/API/v1/cart/add.php

# Get cart
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/cart/get.php

# Get categories
curl -H "Authorization: Bearer $TOKEN" \
     http://your-server.com/API/v1/categories/list.php

# Search products
curl -H "Authorization: Bearer $TOKEN" \
     "http://your-server.com/API/v1/search/products.php?q=shirt"
```

### File Structure Reference
```
API/
├── .env                    # Your configuration (create this)
├── .env.example           # Example configuration
├── .htaccess             # Apache URL rewriting
├── composer.json         # PHP dependencies
├── composer.lock         # (created after composer install)
├── vendor/               # (created after composer install)
├── README.md            # API documentation
├── INSTRUCTIONS.md      # This file
├── config/
│   ├── cors.php         # CORS configuration
│   ├── database.php     # Database connection
│   └── jwt.php          # JWT token management
├── middleware/
│   └── auth.php         # Authentication middleware
└── v1/
    ├── test.php         # Test endpoint
    ├── auth/
    │   └── validate.php # SSO validation (needs implementation)
    ├── products/
    │   ├── list.php     # Product listing
    │   └── detail.php   # Product details
    ├── cart/
    │   ├── get.php      # View cart
    │   └── add.php      # Add to cart
    ├── categories/
    │   └── list.php     # Categories
    ├── checkout/
    │   └── submit.php   # Submit order
    ├── orders/
    │   └── list.php     # Order history
    ├── user/
    │   └── profile.php  # User profile
    ├── search/
    │   └── products.php # Search
    └── shop/
        └── config.php   # Shop configuration
```

---

## Final Notes

1. **Take backups** before making any changes
2. **Test in development** environment first
3. **Implement incrementally** - get basic endpoints working first
4. **Document any customizations** you make
5. **Keep security in mind** at every step

Once you have successfully:
- ✅ Configured the environment
- ✅ Implemented SSO validation
- ✅ Tested all endpoints
- ✅ Deployed to production

The React frontend developer can begin integration immediately.

Good luck with the implementation! The API is well-structured and should integrate smoothly with your existing system.