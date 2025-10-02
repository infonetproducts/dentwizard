# ✅ CORRECT - Add to ALL PHP Files

## Add These EXACT Lines to EVERY PHP File:

```php
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

// REST OF THE EXISTING PHP CODE CONTINUES HERE...
```

## CRITICAL: Must Be Added to ALL These Files in /lg/API/:

### 📁 Products Endpoints
- [ ] `/lg/API/products/list.php`
- [ ] `/lg/API/products/detail.php`
- [ ] `/lg/API/products/search.php` (if exists)
- [ ] `/lg/API/products/featured.php` (if exists)

### 📁 Categories Endpoints  
- [ ] `/lg/API/categories/list.php`
- [ ] `/lg/API/categories/detail.php` (if exists)
- [ ] `/lg/API/categories/products.php` (if exists)

### 📁 Cart Endpoints
- [ ] `/lg/API/cart/get.php`
- [ ] `/lg/API/cart/add.php`
- [ ] `/lg/API/cart/update.php`
- [ ] `/lg/API/cart/remove.php`
- [ ] `/lg/API/cart/clear.php` (if exists)

### 📁 Order Endpoints
- [ ] `/lg/API/orders/create.php`
- [ ] `/lg/API/orders/list.php` (if exists)
- [ ] `/lg/API/orders/detail.php` (if exists)
- [ ] `/lg/API/orders/my-orders.php`
- [ ] `/lg/API/orders/status.php` (if exists)

### 📁 User/Auth Endpoints
- [ ] `/lg/API/user/login.php` (if exists)
- [ ] `/lg/API/user/register.php` (if exists)
- [ ] `/lg/API/user/profile.php`
- [ ] `/lg/API/user/addresses.php`
- [ ] `/lg/API/user/update.php` (if exists)
- [ ] `/lg/API/auth/verify.php` (if exists)

### 📁 Any Other PHP Files
- [ ] ANY other .php files in the API directory
- [ ] Including config files if they output anything

## ⚠️ IMPORTANT RULES:

### ✅ CORRECT Placement:
```php
<?php
// HEADERS MUST BE FIRST - NO BLANK LINES BEFORE
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

// Now the rest of the code
require_once 'config.php';
// etc...
```

### ❌ WRONG - Don't do this:
```php
<?php
require_once 'config.php';  // ❌ WRONG - something before headers
header("Access-Control-Allow-Origin: *");
```

### ❌ WRONG - No blank lines:
```php

<?php  // ❌ WRONG - blank line before <?php
header("Access-Control-Allow-Origin: *");
```

## 🧪 How to Verify It's Working:

After adding to each file, test it:
1. Open http://localhost:3009/quick-test.html
2. Should show ✅ green success
3. Check http://localhost:3009 - products should load

## 💡 Pro Tip:

If your developer has SSH access, they can use the `auto-fix-cors.php` script I provided in the FOR_DEVELOPER folder to automatically add these headers to all files at once.

## 🎯 Bottom Line:

YES - Those exact 4 lines need to be added to EVERY SINGLE PHP file in the API directory, right after the opening <?php tag.
