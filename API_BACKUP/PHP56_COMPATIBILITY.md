# PHP 5.6 Compatibility Guide for API

## Important: Your PHP Version
You're running PHP 5.6, which requires specific adjustments to the API code. This document outlines all the changes made for compatibility.

## Key PHP 5.6 Limitations & Solutions

### 1. NO Type Declarations
❌ PHP 7+ Style:
```php
public function getData(string $id): array {
```

✅ PHP 5.6 Style:
```php
public function getData($id) {
```

### 2. NO Null Coalescing Operator (??)
❌ PHP 7+ Style:
```php
$value = $_GET['id'] ?? 'default';
```

✅ PHP 5.6 Style:
```php
$value = isset($_GET['id']) ? $_GET['id'] : 'default';
```

### 3. Array Syntax (Both work in 5.6, but be consistent)
✅ Both Valid in PHP 5.6:
```php
$array = array('key' => 'value');  // Traditional
$array = ['key' => 'value'];       // Short syntax (PHP 5.4+)
```

### 4. NO Scalar Type Hints
❌ PHP 7+ Style:
```php
function setAge(int $age) {
```

✅ PHP 5.6 Style:
```php
function setAge($age) {
    if (!is_int($age)) {
        throw new InvalidArgumentException('Age must be an integer');
    }
```

### 5. NO Return Type Declarations
❌ PHP 7+ Style:
```php
function getName(): string {
```

✅ PHP 5.6 Style:
```php
function getName() {
    // Document return type in PHPDoc
    /** @return string */
```

## Files Updated for PHP 5.6 Compatibility

### ✅ Updated Files:
1. **config/database.php** - Complete rewrite for PHP 5.6
2. **middleware/auth.php** - Removed type hints, fixed operators
3. **v1/products/list.php** - Compatible array syntax
4. **config/cors.php** - Being updated
5. **config/jwt.php** - Being updated

### 🔄 Files Being Updated:
- All endpoint files in /v1/
- Test files
- Authentication endpoints

## JWT Library for PHP 5.6

Since we need JWT support, install Firebase JWT library (PHP 5.6 compatible version):

```bash
composer require firebase/php-jwt:^5.5
```

This version (5.5) supports PHP 5.6+. Version 6+ requires PHP 7.

## Database Compatibility

Your MySQL functions work fine, but I've added PDO support with fallback:

```php
// Try PDO first (recommended)
$pdo = getPDOConnection();

// Fallback to mysql_* if needed
$conn = getMySQLConnection();
```

## Session Management

PHP 5.6 sessions work perfectly. The API supports both:
- Traditional PHP sessions (existing functionality)
- JWT tokens (for React app)

## Testing PHP 5.6 Compatibility

Run this test to verify PHP version and extensions:

```php
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP 5.6 Check: " . (version_compare(PHP_VERSION, '5.6.0', '>=') ? 'PASS' : 'FAIL') . "\n";
echo "PDO Available: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";
echo "JSON Available: " . (function_exists('json_encode') ? 'YES' : 'NO') . "\n";
echo "Sessions Available: " . (function_exists('session_start') ? 'YES' : 'NO') . "\n";
?>
```

## Common PHP 5.6 Gotchas to Avoid

1. **No Spaceship Operator (<=>)**
   ```php
   // Don't use: $result = $a <=> $b;
   // Use: $result = ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
   ```

2. **No Group Use Declarations**
   ```php
   // Don't use: use App\{Class1, Class2};
   // Use separate lines: 
   use App\Class1;
   use App\Class2;
   ```

3. **Limited Anonymous Class Support**
   - PHP 5.6 doesn't support anonymous classes at all

4. **No Generator Return Expressions**
   - Generators in PHP 5.6 can't use return with a value

## Environment Variables in PHP 5.6

The `getenv()` function works fine, but no `$_ENV` superglobal by default:

```php
// Always use getenv() for safety
$value = getenv('MY_VAR');

// Don't rely on $_ENV
```

## Error Handling

PHP 5.6 doesn't have PHP 7's error handling improvements:

```php
// PHP 5.6 style error handling
try {
    // code
} catch (Exception $e) {
    // handle exception
}

// No multiple catch types in one block
// No Throwable interface
```

## Composer Dependencies

Make sure to use PHP 5.6 compatible versions:

```json
{
    "require": {
        "php": ">=5.6",
        "firebase/php-jwt": "^5.5",
        "vlucas/phpdotenv": "^2.6"
    }
}
```

## Migration Path

Once you upgrade to PHP 7+, you can:
1. Add type declarations
2. Use null coalescing operator
3. Add return types
4. Use newer JWT library versions
5. Improve error handling

## Quick Checklist for Your Developer

- [ ] Verify PHP version is 5.6+
- [ ] Install Composer dependencies (use older versions)
- [ ] No type hints in function parameters
- [ ] No return type declarations
- [ ] Replace ?? with isset() ? : 
- [ ] Use array() or [] consistently
- [ ] Test with PHP 5.6 locally if possible

## Support Timeline

PHP 5.6 end of life was December 31, 2018. Consider upgrading when possible for:
- Security updates
- Better performance
- Modern syntax features
- Latest library versions

## Testing Commands

Test each endpoint with PHP 5.6:

```bash
# Test PHP version
php -v

# Test syntax compatibility
php -l api/v1/products/list.php

# Run test endpoint
php -S localhost:8000
curl http://localhost:8000/api/v1/test.php
```

## Need Help?

If you encounter PHP 5.6 compatibility issues:
1. Check error logs for syntax errors
2. Look for PHP 7+ features used accidentally
3. Test with `php -l filename.php` for syntax check
4. Use `error_reporting(E_ALL)` for debugging