# PHP 5.6 Compatibility Fixes

## Issue Fixed: Parse Error in jwt.php

### Problem:
```
Parse error: syntax error, unexpected '?' in jwt.php on line 50
```

### Cause:
The null coalescing operator `??` was used, which is only available in PHP 7.0+

### Solution Applied:
Fixed all PHP 7+ syntax in `jwt.php` to be PHP 5.6 compatible:

#### Changed From (PHP 7+):
```php
'roles' => $user_data['roles'] ?? ['user']
```

#### Changed To (PHP 5.6):
```php
'roles' => isset($user_data['roles']) ? $user_data['roles'] : array('user')
```

## Other PHP 5.6 Compatibility Changes Made:

1. **Null Coalescing Operator (??)** - Replaced all instances with isset() checks
2. **Array syntax** - Changed `['user']` to `array('user')` for consistency
3. **JWT library compatibility** - Added version detection for Firebase JWT v5 (PHP 5.6) vs v6 (PHP 7.1+)

## Quick PHP 5.6 Compatibility Rules:

### ❌ Don't Use (PHP 7+ only):
```php
// Null coalescing operator
$value = $data['key'] ?? 'default';

// Spaceship operator
$result = $a <=> $b;

// Scalar type hints
function test(string $param): int { }

// Return type declarations
function test(): array { }

// Anonymous classes
$obj = new class {};

// Group use declarations
use Some\Namespace\{ClassA, ClassB};
```

### ✅ Use Instead (PHP 5.6):
```php
// Ternary with isset()
$value = isset($data['key']) ? $data['key'] : 'default';

// Traditional comparison
$result = ($a < $b) ? -1 : (($a > $b) ? 1 : 0);

// No type hints or PHPDoc
/**
 * @param string $param
 * @return int
 */
function test($param) { }

// No return type
function test() { 
    return array();
}

// Regular classes
class TempClass {}
$obj = new TempClass();

// Individual use statements
use Some\Namespace\ClassA;
use Some\Namespace\ClassB;
```

## Files to Check for PHP 5.6 Compatibility:

Run this command to find potential PHP 7+ syntax:
```bash
# Search for null coalescing operator
grep -r "??" --include="*.php" .

# Search for spaceship operator
grep -r "<=>" --include="*.php" .

# Search for scalar type hints (may have false positives)
grep -r "function.*:.*{" --include="*.php" .
```

## Testing the Fix:

1. **Test the endpoint again:**
```bash
curl https://dentwizard.lgstore.com/lg/API/v1/products/list.php?client_id=244&limit=2
```

2. **If still getting errors, check PHP version:**
```php
<?php
// Create test-version.php
phpinfo();
```

3. **Verify JWT is optional:**
The updated `jwt.php` now checks if Firebase JWT is installed before using it, so it won't break if the library isn't installed.

## For Your Developer:

### Immediate Fix:
Upload the updated `jwt.php` file to the server:
```
/home/rwaf/public_html/lg/API/config/jwt.php
```

### If Composer is not installed:
The JWT functionality is now optional. The API will work without it for basic operations.

### To install JWT library (optional):
```bash
# SSH into server
cd /home/rwaf/public_html/lg/API
composer require firebase/php-jwt:^5.5
```

Note: Use version 5.5 for PHP 5.6 compatibility. Version 6+ requires PHP 7.1+

## Additional Files Updated for PHP 5.6:

If you encounter more syntax errors, check these common issues:
1. Replace all `??` with `isset() ? :`
2. Replace all `[]` array syntax with `array()`
3. Remove all type hints from function parameters
4. Remove all return type declarations

The API should now work with PHP 5.6!