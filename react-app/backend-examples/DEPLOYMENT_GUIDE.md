# SSO Deployment Guide

## Overview

This guide walks you through deploying the SSO (SAML 2.0) integration for DentWizard Apparel. Follow these steps in order.

---

## 📦 Prerequisites

### System Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (PHP package manager)
- SSL/HTTPS certificate (for production)
- Access to server file system
- Database administrator access

### Required Information
- Azure AD Tenant ID: `ea1c5a3f-4d62-491a-8ba4-2e9955015493`
- Production URL: `https://dentwizardapparel.com`
- API base path: `/lg/API/v1/`

---

## 🚀 Step-by-Step Deployment

### Phase 1: Database Setup

#### 1.1 Backup Current Database
```bash
mysqldump -u root -p dentwizard_db > backup_before_sso_$(date +%Y%m%d).sql
```

#### 1.2 Run Database Migrations
```bash
mysql -u root -p dentwizard_db < backend-examples/database-schema.sql
```

#### 1.3 Verify Schema Changes
```sql
-- Check if columns were added
DESCRIBE users;

-- Should see:
-- auth_type ENUM('standard', 'sso')
-- azure_ad_object_id VARCHAR(255) NULL
-- last_login DATETIME NULL
-- password VARCHAR(255) NULL
```

#### 1.4 Create Initial Test SSO User
```sql
INSERT INTO users (
    email,
    first_name,
    last_name,
    auth_type,
    password,
    budget,
    is_active,
    created_at
) VALUES (
    'test.user@dentwizard.com',  -- Replace with real test user
    'Test',
    'User',
    'sso',
    NULL,
    500.00,
    1,
    NOW()
);
```

---

### Phase 2: Backend PHP Files

#### 2.1 Install Composer Dependencies
```bash
cd /path/to/your/api/root
composer require onelogin/php-saml
```

If you don't have `composer.json`, copy it first:
```bash
cp backend-examples/composer.json composer.json
composer install
```

#### 2.2 Copy Backend Files to Production

**File Structure:**
```
/lg/API/v1/
├── auth/
│   ├── check-user.php         (COPY)
│   ├── login-token.php        (EXISTING - keep)
│   └── saml/
│       ├── login.php          (COPY)
│       ├── callback.php       (COPY)
│       └── logout.php         (COPY)
├── config/
│   └── saml-config.php        (COPY)
├── helpers/
│   └── saml-helpers.php       (COPY)
└── vendor/                    (Created by composer)
```

**Copy commands:**
```bash
# Create directories
mkdir -p /lg/API/v1/auth/saml
mkdir -p /lg/API/v1/config
mkdir -p /lg/API/v1/helpers

# Copy files
cp php-backend-examples/check-user.php /lg/API/v1/auth/
cp php-backend-examples/saml-login.php /lg/API/v1/auth/saml/login.php
cp php-backend-examples/saml-callback.php /lg/API/v1/auth/saml/callback.php
cp backend-examples/config/saml-config.php /lg/API/v1/config/
cp backend-examples/helpers/saml-helpers.php /lg/API/v1/helpers/
```

#### 2.3 Configure Environment Variables

Create or update `/lg/API/v1/.env`:
```bash
# Database
DB_HOST=localhost
DB_NAME=dentwizard_db
DB_USER=your_db_user
DB_PASS=your_db_password

# JWT Secret (generate a secure random string)
JWT_SECRET=your-secret-key-change-this-to-random-string

# React App URL
REACT_APP_URL=https://dentwizardapparel.com

# Azure AD
AZURE_TENANT_ID=ea1c5a3f-4d62-491a-8ba4-2e9955015493
```

**Generate secure JWT secret:**
```bash
php -r "echo bin2hex(random_bytes(32));"
```

#### 2.4 Update SAML Configuration

Edit `/lg/API/v1/config/saml-config.php`:
```php
// Update these URLs for production
'sp' => [
    'entityId' => 'https://dentwizardapparel.com',
    'assertionConsumerService' => [
        'url' => 'https://dentwizardapparel.com/lg/API/v1/auth/saml/callback',
    ],
    'singleLogoutService' => [
        'url' => 'https://dentwizardapparel.com/lg/API/v1/auth/saml/logout',
    ],
],
```

#### 2.5 Set File Permissions
```bash
chmod 755 /lg/API/v1/auth/saml/*.php
chmod 644 /lg/API/v1/config/saml-config.php
chmod 644 /lg/API/v1/helpers/saml-helpers.php
chmod 600 /lg/API/v1/.env  # Secure the environment file
```

---

### Phase 3: React Frontend Deployment

#### 3.1 Update Production Environment

Edit `.env.production`:
```bash
REACT_APP_USE_MOCK_AUTH=false
REACT_APP_API_URL=https://dentwizardapparel.com/lg/API/v1
REACT_APP_SSO_ENTITY_ID=https://dentwizardapparel.com
REACT_APP_SSO_CALLBACK_URL=https://dentwizardapparel.com/auth/sso-callback
REACT_APP_SSO_LOGOUT_URL=https://dentwizardapparel.com/auth/sso-logout
REACT_APP_AZURE_TENANT_ID=ea1c5a3f-4d62-491a-8ba4-2e9955015493
```

#### 3.2 Build React App
```bash
cd react-app
npm run build
```

#### 3.3 Deploy Build Files
```bash
# Copy build files to web server
cp -r build/* /path/to/web/root/
```

---

### Phase 4: Azure AD Configuration

#### 4.1 Provide Information to DentWizard IT

Send them this information:

```
Subject: SSO Configuration for DentWizard Apparel

Please configure our application in your Azure AD:

Enterprise Application Name: DentWizard Apparel

SAML Settings:
├── Entity ID (Identifier): https://dentwizardapparel.com
├── Reply URL (ACS URL): https://dentwizardapparel.com/lg/API/v1/auth/saml/callback
├── Sign-on URL: https://dentwizardapparel.com/login
└── Logout URL: https://dentwizardapparel.com/lg/API/v1/auth/saml/logout

User Claims Mapping:
├── Email: http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress
├── First Name: http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname
├── Last Name: http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname
└── Object ID: http://schemas.microsoft.com/identity/claims/objectidentifier

Please provide:
1. Confirmation when configuration is complete
2. List of test users we can use for testing
```

#### 4.2 Update Federation Metadata (if needed)

If DentWizard provides updated metadata XML:
1. Save as `LeaderGraphics.xml` 
2. Extract certificate
3. Update `/lg/API/v1/config/saml-config.php` with new certificate

---

### Phase 5: Testing

#### 5.1 Test Standard Login (Existing Users)
```
1. Go to https://dentwizardapparel.com/login
2. Enter existing user email (not @dentwizard.com)
3. Enter password
4. Should login successfully ✓
```

#### 5.2 Test SSO Login
```
1. Go to https://dentwizardapparel.com/login
2. Enter test.user@dentwizard.com
3. Click "Sign in with Microsoft"
4. Login with Azure AD credentials
5. Should redirect back and login successfully ✓
```

#### 5.3 Test SSO Error Cases

**User Not in Database:**
```
1. Login with new.user@dentwizard.com (not in DB)
2. Should show: "Your account has not been set up yet"
```

**Inactive User:**
```sql
UPDATE users SET is_active = 0 WHERE email = 'test.user@dentwizard.com';
```
```
1. Try to login with test.user@dentwizard.com
2. Should show: "Your account has been deactivated"
```

**Wrong Auth Type:**
```sql
UPDATE users SET auth_type = 'standard' WHERE email = 'test.user@dentwizard.com';
```
```
1. Try to login via SSO
2. Should show: "Not configured for SSO login"
```

#### 5.4 Test API Calls
```
1. Login via SSO
2. Browse to products page
3. Add item to cart
4. Check that budget displays
5. Verify API calls work with JWT token
```

---

### Phase 6: Monitoring & Maintenance

#### 6.1 Set Up Logging

Check that logs are being created:
```bash
tail -f /var/log/php_errors.log
```

Query audit log:
```sql
SELECT * FROM sso_audit_log ORDER BY created_at DESC LIMIT 10;
```

#### 6.2 Set Certificate Expiration Reminder

**IMPORTANT:** Azure AD signing certificate expires **September 10, 2028**

Set calendar reminders:
- August 10, 2028 (30 days before)
- September 1, 2028 (10 days before)

#### 6.3 Create Monitoring Script (Optional)

`/lg/API/v1/scripts/check-sso-health.php`:
```php
<?php
// Check SSO health
$checks = [
    'database' => checkDatabase(),
    'saml_lib' => checkSAMLLibrary(),
    'config' => checkSAMLConfig(),
    'cert_expiry' => checkCertExpiration()
];

// Send alert if issues found
if (hasIssues($checks)) {
    sendAlert($checks);
}
```

---

## 🔧 Troubleshooting

### Issue: "Class 'OneLogin\Saml2\Auth' not found"
**Solution:** Composer dependencies not installed
```bash
cd /lg/API/v1
composer install
```

### Issue: "Database connection failed"
**Solution:** Check `.env` database credentials
```bash
cat /lg/API/v1/.env
# Verify DB_HOST, DB_NAME, DB_USER, DB_PASS
```

### Issue: "SAML validation failed"
**Solution:** Check Azure AD certificate
```bash
# Extract cert from config
grep 'x509cert' /lg/API/v1/config/saml-config.php
```

### Issue: Users getting "Account not set up"
**Solution:** Create users in database first (see ADMIN_GUIDE.md)

### Issue: Token not working for API calls
**Solution:** Verify JWT secret matches
```bash
# Check JWT_SECRET in .env
# Ensure same secret used in token generation and validation
```

---

## 📋 Post-Deployment Checklist

- [ ] Database schema updated successfully
- [ ] Composer dependencies installed
- [ ] All PHP files copied to correct locations
- [ ] Environment variables configured
- [ ] SAML configuration updated for production URLs
- [ ] File permissions set correctly
- [ ] React app built and deployed
- [ ] Azure AD configuration provided to DentWizard
- [ ] Test standard login works
- [ ] Test SSO login works
- [ ] Test error cases handled properly
- [ ] API calls work with SSO tokens
- [ ] Audit logging working
- [ ] Certificate expiration reminder set
- [ ] Admin guide shared with team
- [ ] Backup of database taken

---

## 🆘 Rollback Plan

If SSO causes issues:

### Quick Rollback (Disable SSO)
1. Remove SSO route from React app
2. Restore old LoginPage component
3. Standard login still works ✓

### Full Rollback (Restore Database)
```bash
mysql -u root -p dentwizard_db < backup_before_sso_YYYYMMDD.sql
```

**Note:** This will lose any SSO users added after backup

---

## 📞 Support Contacts

**For deployment issues:**
- Development team

**For Azure AD configuration:**
- DentWizard IT Department

**For database issues:**
- Database Administrator

**For user account issues:**
- See ADMIN_GUIDE.md
