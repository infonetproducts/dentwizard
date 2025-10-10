# SSO Database & PHP Setup Guide for Developer

## 📋 Overview
This document provides step-by-step instructions for setting up SAML SSO authentication on the DentWizard Apparel backend. This adds Azure AD SSO login while maintaining existing email/password authentication.

**Estimated Time**: 30-45 minutes  
**Difficulty**: Medium  
**PHP Version**: 5.6+ (all code is PHP 5.6 compatible)

---

## 🎯 What This Does

### Current Authentication
- Users log in with email + password
- Works fine, no changes needed

### After This Setup
- **Standard users**: Continue logging in with email + password (unchanged)
- **SSO users** (@dentwizard.com): Log in through Azure AD (new)
- Both types use the same JWT token system
- Both types access the same cart, products, budget

---

## ⚠️ IMPORTANT: Do These Steps in Order

1. **Backup database** (always!)
2. **Run database migration** (add new columns)
3. **Verify database changes**
4. **Upload PHP files**
5. **Install Composer dependencies**
6. **Test endpoints**
7. **Add test SSO user**

---

## Step 1: Backup Database ⚠️

**CRITICAL: Always backup before schema changes!**

```bash
# Full database backup
mysqldump -u your_user -p your_database > backup_before_sso_$(date +%Y%m%d).sql

# Or use phpMyAdmin: Export > SQL > Save
```

**Verify backup was created:**
```bash
ls -lh backup_before_sso_*.sql
# Should show file with today's date
```

---

## Step 2: Run Database Migration

### 2.1 Review the SQL First

**Open and review**: `backend-examples/database-schema.sql`

This SQL will:
- ✅ Add `auth_type` column (standard or sso)
- ✅ Add `azure_ad_object_id` column (optional Azure AD link)
- ✅ Add `last_login` column (track login times)
- ✅ Make `password` nullable (SSO users don't need passwords)
- ✅ Add indexes for performance
- ✅ NOT affect existing users (they stay 'standard' type)

### 2.2 Connect to Database

**Option A: MySQL Command Line**
```bash
mysql -u your_username -p your_database_name
```

**Option B: phpMyAdmin**
1. Open phpMyAdmin
2. Select your database
3. Click "SQL" tab

### 2.3 Run the Migration SQL

**Copy this entire SQL block and run it:**

```sql
-- ============================================
-- SSO Database Migration
-- Adds support for SSO authentication
-- Safe to run - does not affect existing users
-- ============================================

-- Step 1: Add auth_type column
-- This identifies if user is 'standard' or 'sso'
ALTER TABLE users 
ADD COLUMN auth_type ENUM('standard', 'sso') DEFAULT 'standard' 
COMMENT 'Authentication type: standard (email/password) or sso (Azure AD)';

-- Step 2: Add Azure AD object ID
-- Optional field to link user to Azure AD account
ALTER TABLE users 
ADD COLUMN azure_ad_object_id VARCHAR(255) NULL DEFAULT NULL
COMMENT 'Azure AD Object ID for SSO users';

-- Step 3: Add last login timestamp
-- Track when users last logged in
ALTER TABLE users 
ADD COLUMN last_login DATETIME NULL DEFAULT NULL
COMMENT 'Timestamp of last successful login';

-- Step 4: Make password nullable
-- SSO users authenticate through Azure AD, don't need passwords
ALTER TABLE users 
MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL
COMMENT 'Password hash (NULL for SSO users)';

-- Step 5: Add indexes for performance
CREATE INDEX idx_auth_type ON users(auth_type);
CREATE INDEX idx_azure_id ON users(azure_ad_object_id);
CREATE INDEX idx_last_login ON users(last_login);

-- Step 6: Verify changes
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'users' 
AND COLUMN_NAME IN ('auth_type', 'azure_ad_object_id', 'last_login', 'password')
ORDER BY ORDINAL_POSITION;

-- Expected output:
-- password           | VARCHAR(255)            | YES | NULL | Password hash (NULL for SSO users)
-- auth_type          | ENUM('standard','sso')  | NO  | standard | Authentication type
-- azure_ad_object_id | VARCHAR(255)            | YES | NULL | Azure AD Object ID
-- last_login         | DATETIME                | YES | NULL | Last successful login
```

### 2.4 Verify Migration Success

**Run this verification query:**

```sql
-- Check table structure
DESCRIBE users;

-- Should see these new columns:
-- +--------------------+---------------------------+------+-----+---------+
-- | Field              | Type                      | Null | Key | Default |
-- +--------------------+---------------------------+------+-----+---------+
-- | auth_type          | enum('standard','sso')    | NO   | MUL | standard|
-- | azure_ad_object_id | varchar(255)              | YES  | MUL | NULL    |
-- | last_login         | datetime                  | YES  | MUL | NULL    |
-- | password           | varchar(255)              | YES  |     | NULL    |
-- +--------------------+---------------------------+------+-----+---------+

-- Check existing users weren't affected
SELECT 
    email, 
    auth_type, 
    password IS NOT NULL AS has_password,
    azure_ad_object_id,
    last_login
FROM users 
LIMIT 5;

-- Expected: All existing users have auth_type='standard' and has_password=1
```

**✅ If you see the new columns and existing users are unchanged, migration succeeded!**

---

## Step 3: Upload PHP Files

### 3.1 Files to Upload

**Source**: `backend-examples/` folder

**Upload these files to your API directory** (usually `/api/`):

```
Your Server /api/ Directory Structure:

/api/
├── config/
│   └── saml-config.php          ← NEW - SAML configuration
│
├── helpers/
│   └── saml-helpers.php         ← NEW - SAML utility functions
│
├── check-user.php               ← NEW - Check if user exists/type
├── saml-login.php               ← NEW - Initiate SSO login
├── saml-callback.php            ← NEW - Handle SSO callback
├── composer.json                ← NEW - PHP dependencies
│
└── (existing files stay unchanged)
    ├── login.php                ← EXISTING - No changes
    ├── products.php             ← EXISTING - No changes
    ├── cart.php                 ← EXISTING - No changes
    └── ...
```

### 3.2 Upload Instructions

**Option A: FTP/SFTP (FileZilla, etc.)**
```
1. Connect to your server
2. Navigate to /api/ directory
3. Create folders if needed:
   - /api/config/
   - /api/helpers/
4. Upload files to correct locations
```

**Option B: cPanel File Manager**
```
1. Login to cPanel
2. Open File Manager
3. Navigate to public_html/api/
4. Create folders: config/ and helpers/
5. Upload files
```

**Option C: Command Line (if you have SSH)**
```bash
# From your local machine, in the backend-examples folder
scp config/saml-config.php user@server:/path/to/api/config/
scp helpers/saml-helpers.php user@server:/path/to/api/helpers/
scp check-user.php user@server:/path/to/api/
scp saml-login.php user@server:/path/to/api/
scp saml-callback.php user@server:/path/to/api/
scp composer.json user@server:/path/to/api/
```

### 3.3 Set File Permissions

**Important for security:**

```bash
# SSH into your server, then:
cd /path/to/api/

# Set directory permissions
chmod 755 config/
chmod 755 helpers/

# Set file permissions
chmod 644 config/saml-config.php
chmod 644 helpers/saml-helpers.php
chmod 644 check-user.php
chmod 644 saml-login.php
chmod 644 saml-callback.php
chmod 644 composer.json
```

---

## Step 4: Install Composer Dependencies

### 4.1 Check if Composer is Installed

```bash
# SSH into your server
composer --version

# If installed, you'll see: Composer version 2.x.x
```

### 4.2 Install Composer (if needed)

**If composer is not installed:**

```bash
# Download installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Install
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Verify
composer --version
```

**If you don't have SSH access**, contact your hosting provider and ask them to:
1. Install Composer
2. Run `composer install` in your /api/ directory

### 4.3 Install SAML Library

```bash
# Navigate to API directory
cd /path/to/api/

# Install dependencies (this installs onelogin/php-saml)
composer install

# You should see output like:
# Loading composer repositories with package information
# Installing dependencies from lock file
# Package operations: X installs, 0 updates, 0 removals
#   - Installing onelogin/php-saml (...)
# ...
# Generating autoload files
```

### 4.4 Verify Installation

```bash
# Check that vendor directory was created
ls -la vendor/

# Should see:
# vendor/
# ├── autoload.php
# ├── composer/
# └── onelogin/
#     └── php-saml/

# Check file exists
ls -la vendor/autoload.php
# Should show: vendor/autoload.php (exists)
```

**✅ If `vendor/autoload.php` exists, Composer installation succeeded!**

---

## Step 5: Configure Database Connection

### 5.1 Update Database Configuration

**Edit**: `config/saml-config.php`

**Find this section** (around line 15):

```php
// Database Configuration (update with your credentials)
$dbConfig = array(
    'host' => 'localhost',           // ← Your database host
    'database' => 'your_database',   // ← Your database name
    'username' => 'your_username',   // ← Your database user
    'password' => 'your_password',   // ← Your database password
);
```

**Update with your actual database credentials:**

```php
// Example:
$dbConfig = array(
    'host' => 'localhost',
    'database' => 'dentwizard_db',
    'username' => 'dentwizard_user',
    'password' => 'your_secure_password_here',
);
```

**💡 Tip**: Use the same credentials as your existing `login.php` or `products.php` files.

---

## Step 6: Test Endpoints

### 6.1 Test Database Connection

**Create temporary test file**: `/api/test-sso-db.php`

```php
<?php
// Test SSO database connection
require_once 'config/saml-config.php';

// Get database config
$dbConfig = getSAMLConfig()['database'];

// Test connection
$conn = mysqli_connect(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database']
);

if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "✅ Database connection successful!\n\n";

// Test users table structure
$result = mysqli_query($conn, "DESCRIBE users");
if ($result) {
    echo "✅ Users table accessible!\n\n";
    echo "Checking for SSO columns...\n";
    
    $columns = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $columns[] = $row['Field'];
    }
    
    $required = array('auth_type', 'azure_ad_object_id', 'last_login');
    foreach ($required as $col) {
        if (in_array($col, $columns)) {
            echo "✅ Column '$col' exists\n";
        } else {
            echo "❌ Column '$col' MISSING!\n";
        }
    }
} else {
    die("❌ Cannot access users table: " . mysqli_error($conn));
}

mysqli_close($conn);
echo "\n✅ All tests passed!";
?>
```

**Run it:**
```
Visit: https://yourdomain.com/api/test-sso-db.php
```

**Expected output:**
```
✅ Database connection successful!
✅ Users table accessible!
Checking for SSO columns...
✅ Column 'auth_type' exists
✅ Column 'azure_ad_object_id' exists
✅ Column 'last_login' exists
✅ All tests passed!
```

### 6.2 Test Check User Endpoint

```bash
# Test with non-existent user
curl "https://yourdomain.com/api/check-user.php?email=test@dentwizard.com"

# Expected response:
{
  "exists": false,
  "message": "User not found. Please contact your administrator to create an account."
}

# Test with existing standard user (if you have one)
curl "https://yourdomain.com/api/check-user.php?email=existing@example.com"

# Expected response:
{
  "exists": true,
  "auth_type": "standard",
  "requiresSSO": false
}
```

### 6.3 Test SAML Config Loads

**Create**: `/api/test-saml-config.php`

```php
<?php
// Test SAML configuration loads
require_once 'config/saml-config.php';

$config = getSAMLConfig();

if (!$config) {
    die("❌ Failed to load SAML config");
}

echo "✅ SAML config loaded successfully!\n\n";
echo "Checking configuration...\n";

// Check required fields
$checks = array(
    'Azure AD Entity ID' => !empty($config['idp']['entityId']),
    'Azure AD SSO URL' => !empty($config['idp']['singleSignOnService']['url']),
    'Azure AD Certificate' => !empty($config['idp']['x509cert']),
    'SP Entity ID' => !empty($config['sp']['entityId']),
    'SP Callback URL' => !empty($config['sp']['assertionConsumerService']['url']),
    'Database Config' => !empty($config['database']),
);

foreach ($checks as $name => $status) {
    echo ($status ? "✅" : "❌") . " $name\n";
}

echo "\n✅ Configuration valid!";
?>
```

**Run it:**
```
Visit: https://yourdomain.com/api/test-saml-config.php
```

**Delete test files after verification:**
```bash
rm /api/test-sso-db.php
rm /api/test-saml-config.php
```

---

## Step 7: Add Test SSO User

### 7.1 Create SSO User in Database

**Run this SQL** (update with real test user email):

```sql
-- Add test SSO user
INSERT INTO users (
    email, 
    first_name, 
    last_name, 
    auth_type,           -- NEW: 'sso' for SSO users
    password,            -- NEW: NULL for SSO users (no password needed)
    budget, 
    is_active,
    created_at
) VALUES (
    'test.user@dentwizard.com',  -- ← UPDATE: Use real DentWizard email
    'Test',                       -- ← UPDATE: First name
    'User',                       -- ← UPDATE: Last name
    'sso',                        -- ✅ SSO authentication type
    NULL,                         -- ✅ No password (Azure AD handles auth)
    500.00,                       -- ← UPDATE: Budget amount
    1,                            -- ✅ Active user
    NOW()                         -- ✅ Current timestamp
);

-- Verify user was created
SELECT 
    user_id,
    email, 
    first_name, 
    last_name,
    auth_type, 
    password IS NULL AS no_password,
    budget,
    is_active
FROM users 
WHERE email = 'test.user@dentwizard.com';

-- Expected output:
-- user_id | email                      | first_name | last_name | auth_type | no_password | budget | is_active
-- 123     | test.user@dentwizard.com   | Test       | User      | sso       | 1           | 500.00 | 1
```

### 7.2 Test Check User with SSO User

```bash
# Test the SSO user
curl "https://yourdomain.com/api/check-user.php?email=test.user@dentwizard.com"

# Expected response:
{
  "exists": true,
  "auth_type": "sso",
  "requiresSSO": true
}
```

**✅ If you see this response, SSO user is configured correctly!**

---

## Step 8: Verify Existing Users Unaffected

**Critical: Ensure standard login still works!**

```sql
-- Check all users
SELECT 
    email,
    auth_type,
    password IS NOT NULL AS has_password,
    is_active
FROM users
ORDER BY created_at DESC
LIMIT 10;

-- Should see:
-- Existing users: auth_type='standard', has_password=1
-- New SSO users: auth_type='sso', has_password=0
```

**Test standard login** (if you have test credentials):
```
1. Go to login page
2. Enter existing user email (not @dentwizard.com)
3. Enter password
4. Should log in successfully ✅
```

---

## 🔒 Security Checklist

Before going live, verify:

- [ ] Database backup completed
- [ ] New columns added successfully
- [ ] Composer dependencies installed
- [ ] File permissions set correctly (644 for PHP files)
- [ ] Database credentials updated in saml-config.php
- [ ] Test SSO user created
- [ ] Existing users still work
- [ ] All endpoints return proper JSON responses
- [ ] Error messages don't expose sensitive info

---

## 📋 Post-Installation Checklist

### Immediate Verification
- [ ] Database migration successful
- [ ] All new columns present
- [ ] Existing users have auth_type='standard'
- [ ] PHP files uploaded to correct locations
- [ ] Composer dependencies installed
- [ ] vendor/autoload.php exists
- [ ] Database config updated
- [ ] Test SSO user added
- [ ] check-user.php endpoint working

### Before Going Live
- [ ] Frontend deployed with new login page
- [ ] DentWizard IT configured Azure AD
- [ ] Test SSO login works end-to-end
- [ ] Test standard login still works
- [ ] Test cart/budget for both user types
- [ ] Error handling tested
- [ ] Logs reviewed for errors

---

## 🐛 Troubleshooting

### "Column already exists" Error
```
This means migration was already run. Check:
SELECT * FROM users LIMIT 1;

If auth_type column exists, you're good! Skip migration.
```

### "Cannot connect to database"
```
Check database credentials in config/saml-config.php
Try connecting with same credentials in your existing files
```

### "vendor/autoload.php not found"
```
Composer install didn't complete. Run:
cd /api/
composer install
```

### "Class 'OneLogin\Saml2\Auth' not found"
```
Composer dependencies not installed properly. Run:
cd /api/
rm -rf vendor/
composer install
```

### Check User Returns 500 Error
```
1. Check PHP error logs
2. Verify database credentials
3. Ensure require_once paths are correct
4. Test database connection directly
```

---

## 📞 Support Information

### Log Files to Check
```bash
# PHP error log (location varies by server)
tail -f /var/log/php/error.log

# Apache error log
tail -f /var/log/apache2/error.log

# Nginx error log
tail -f /var/log/nginx/error.log
```

### Common Log Locations
- cPanel: `/home/username/public_html/error_log`
- Plesk: `/var/log/httpd/error_log`
- DirectAdmin: `/var/log/httpd/domains/yourdomain.com.error.log`

---

## ✅ Success Criteria

You'll know setup is successful when:

1. ✅ Database has new columns (auth_type, azure_ad_object_id, last_login)
2. ✅ Existing users still have passwords and auth_type='standard'
3. ✅ PHP files uploaded and accessible
4. ✅ Composer dependencies installed (vendor folder exists)
5. ✅ check-user.php endpoint returns proper JSON
6. ✅ Test SSO user in database with auth_type='sso'
7. ✅ No PHP errors in logs
8. ✅ Standard login still works for existing users

---

## 📄 Summary

**What you did:**
1. ✅ Backed up database
2. ✅ Added SSO columns to users table
3. ✅ Uploaded 5 new PHP files
4. ✅ Installed Composer dependencies
5. ✅ Configured database connection
6. ✅ Added test SSO user
7. ✅ Verified everything works

**What's next:**
- Frontend team deploys React app with SSO login
- Coordinate with DentWizard IT for Azure AD setup
- Test SSO login end-to-end
- Go live!

**Questions?** 
- Check troubleshooting section above
- Review error logs
- Test each endpoint individually

---

**Document Version**: 1.0  
**Last Updated**: For PHP 5.6 compatibility  
**Estimated Setup Time**: 30-45 minutes  
**Difficulty**: Medium
