# 🚨 URGENT: JWT.PHP FIX OPTIONS

## Current Error:
```
Parse error: syntax error, unexpected 'use' (T_USE) in jwt.php on line 12
```

## You Have 3 Options to Fix This:

### Option 1: SIMPLEST - Use jwt-simple.php (Recommended for Now)
**This will get you running immediately!**

```bash
# On the server, rename files:
cd /home/rwaf/public_html/lg/API/config
mv jwt.php jwt-broken.php.bak
```

Then upload `jwt-simple.php` as `jwt.php`:
- **From:** `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\config\jwt-simple.php`
- **To:** `/home/rwaf/public_html/lg/API/config/jwt.php`

✅ **Pros:** Works immediately, no libraries needed
❌ **Cons:** Not secure (fine for testing)

---

### Option 2: Use Fixed jwt.php (No use statements)
Upload the latest fixed version:
- **From:** `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\config\jwt.php`
- **To:** `/home/rwaf/public_html/lg/API/config/jwt.php`

✅ **Pros:** Works with or without Firebase JWT
❌ **Cons:** Might still have issues if JWT library is partially installed

---

### Option 3: Remove JWT Completely (Temporary)
Edit each endpoint that's failing and comment out JWT:

```php
// In files like products/list.php, comment out:
// require_once '../../middleware/auth.php';
```

✅ **Pros:** Bypasses JWT entirely
❌ **Cons:** Have to edit multiple files

---

## 🎯 RECOMMENDED ACTION:

**Use Option 1 (jwt-simple.php)** to get everything working NOW, then you can deal with proper JWT setup later.

```bash
# Quick commands for Option 1:
ssh user@server
cd /home/rwaf/public_html/lg/API/config
mv jwt.php jwt.backup.php
# Then upload jwt-simple.php as jwt.php via FTP
```

## Test It Works:
```bash
curl https://dentwizard.lgstore.com/lg/API/v1/products/list.php?client_id=244&limit=2
```

This should return product data instead of an error!