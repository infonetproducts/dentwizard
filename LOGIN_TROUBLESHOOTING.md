# LOGIN TROUBLESHOOTING GUIDE

## 🚨 The Problem:
- Login appears to succeed but session isn't being set
- Session shows empty: `"all_session_data": []`
- Profile showing wrong user data

## 📦 UPLOAD THESE FILES IMMEDIATELY:

### 1. Test Files (to /lg/API/v1/auth/):
- **login-test.php** - Enhanced login with debugging
- **force-login-jamie.php** - Manually sets Jamie's session
- **test-db-session.php** - Shows current session state

## 🔧 TROUBLESHOOTING STEPS:

### Option 1: Force Login (Quick Fix)
1. Upload `force-login-jamie.php`
2. Visit: https://dentwizard.lgstore.com/lg/API/v1/auth/force-login-jamie.php
3. This will manually set Jamie's session
4. Then go to http://localhost:3000 - you should be logged in

### Option 2: Test New Login Endpoint
1. Upload `login-test.php`
2. Test it directly first:
   ```
   Visit: https://dentwizard.lgstore.com/lg/API/v1/auth/login-test.php
   (Shows current session in GET mode)
   ```
3. Try logging in through React app:
   - Email: jkrugger@infonetproducts.com
   - Password: password

### Option 3: Debug Session Issues
Visit these URLs in order:
1. https://dentwizard.lgstore.com/lg/API/v1/auth/test-db-session.php
   (Check current session)
2. https://dentwizard.lgstore.com/lg/API/v1/auth/force-login-jamie.php
   (Force set the session)
3. https://dentwizard.lgstore.com/lg/API/v1/auth/test-db-session.php
   (Verify session is now set)

## 🔍 Common Session Issues:

### PHP Session Not Persisting
This could be due to:
- Session cookies not being sent/received
- Session path mismatch
- PHP configuration issues

### Quick Fix - Add to All PHP Files:
```php
// At the very beginning of each PHP file
ini_set('session.cookie_domain', '.lgstore.com');
ini_set('session.cookie_path', '/');
session_start();
```

## 🎯 IMMEDIATE ACTION:
1. Upload the 3 test files
2. Visit force-login-jamie.php to manually set session
3. Check if you can now see Jamie's data in the app

## 📝 If Still Not Working:
Share the output from:
1. https://dentwizard.lgstore.com/lg/API/v1/auth/test-db-session.php
2. Browser's Network tab when trying to login
3. Any PHP error logs from your server

## 🚀 Alternative - Direct Database Test:
Run this SQL to verify Jamie's data:
```sql
SELECT ID, Email, Name, Budget, BudgetBalance 
FROM Users 
WHERE Email = 'jkrugger@infonetproducts.com';
```

Expected result:
- ID: 20296
- Name: Jamie Krugger
- Should have budget values

## ✅ Success Indicators:
When it's working, test-db-session.php should show:
- session_id with a value
- user_id: 20296
- user_email: jkrugger@infonetproducts.com
- user_name: Jamie Krugger
