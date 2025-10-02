# PHP 5.6 API Quick Reference Card

## Essential Commands
```bash
php -v                          # Check PHP version (must be 5.6+)
composer install                # Install dependencies
php -l file.php                # Check PHP syntax
php -S localhost:8000          # Start test server
```

## PHP 5.6 Conversion Rules
| ❌ Don't Use (PHP 7+) | ✅ Use Instead (PHP 5.6) |
|----------------------|-------------------------|
| `function foo(string $x): array` | `function foo($x)` |
| `$val = $_GET['x'] ?? 'default'` | `$val = isset($_GET['x']) ? $_GET['x'] : 'default'` |
| `$a <=> $b` | `($a < $b) ? -1 : (($a > $b) ? 1 : 0)` |
| `use App\{Class1, Class2}` | `use App\Class1;`<br>`use App\Class2;` |

## API Endpoints
```
BASE_URL: https://your-server.com/API/v1

GET  /test.php                    # System health check
GET  /products/list.php           # List products
GET  /products/detail.php?id=X    # Product details
POST /cart/add.php                # Add to cart
GET  /cart/get.php                # View cart
POST /auth/validate.php           # SSO validation
POST /checkout/submit.php         # Complete order
GET  /orders/list.php             # Order history
```

## Testing with cURL
```bash
# Test endpoint (no auth)
curl http://your-server.com/API/v1/test.php

# Get products
curl "http://your-server.com/API/v1/products/list.php?client_id=1"

# Add to cart (POST)
curl -X POST http://your-server.com/API/v1/cart/add.php \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":2}'

# With authentication
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://your-server.com/API/v1/products/list.php
```

## Database Connection (.env)
```ini
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_username
DB_PASS=your_password
JWT_SECRET=random-32-char-string-here
BASE_URL=https://your-server.com
```

## Required PHP Extensions
```bash
php -m | grep -E 'pdo_mysql|json|curl|session|openssl'
```

## JWT Token Format
```javascript
// Header.Payload.Signature
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.
eyJ1c2VyX2lkIjoxLCJlbWFpbCI6InVzZXJAZXhhbXBsZS5jb20ifQ.
signature_here
```

## Common Errors & Fixes
| Error | Solution |
|-------|----------|
| `Class not found` | Run `composer install` |
| `500 Internal Server` | Check PHP error log |
| `CORS blocked` | Update config/cors.php |
| `DB connection failed` | Check .env credentials |
| `Invalid token` | Verify JWT_SECRET matches |
| `Syntax error` | Run `php -l filename.php` |

## File Structure
```
API/
├── config/
│   ├── cors.php         # CORS headers
│   ├── database.php     # DB connection
│   └── .env            # Your credentials
├── middleware/
│   └── auth.php        # JWT handling
├── v1/
│   ├── test.php        # Test this first!
│   ├── products/       # Product endpoints
│   ├── cart/          # Cart operations
│   └── auth/          # SSO validation
└── composer.json       # Dependencies
```

## Debug Mode
```php
// Add to top of file for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Remove in production!
```

## SSO Providers Quick Setup
```php
// Auth0
composer require auth0/auth0-php:^5.0

// Okta  
composer require okta/jwt-verifier:^1.0

// Azure AD
composer require firebase/php-jwt:^5.5
```

## Session vs JWT
```php
// Check both authentication methods
if (isset($_SESSION['user_id'])) {
    // Traditional session
} else if ($jwt_token) {
    // JWT authentication
}
```

## Response Format
```json
{
  "success": true,
  "data": {
    "products": [...],
    "pagination": {...}
  },
  "error": null
}
```

## Production Checklist
- [ ] Change JWT_SECRET
- [ ] Set ENV=production  
- [ ] Disable error display
- [ ] Update CORS origins
- [ ] Enable HTTPS only
- [ ] Add rate limiting

## Time Estimates
- Setup: 30 min
- Database: 30 min
- SSO: 2-4 hours
- Testing: 1 hour
- **Total: 6-8 hours**

## Help Commands
```bash
php -i                 # PHP configuration
php -m                 # Installed modules
mysql -u user -p       # Test DB connection
tail -f error.log      # Watch error log
```

---
**Remember: PHP 5.6 = No type hints, No ??, isset() everything!**