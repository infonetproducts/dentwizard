# SSO Quick Reference Guide

## 🎯 What Was Built

A complete SAML 2.0 SSO integration that allows DentWizard employees to login using their Microsoft/Azure AD accounts, while maintaining backward compatibility with existing PHP user authentication.

---

## 📁 File Locations

### React Frontend
```
src/
├── config/samlConfig.js              → Azure AD metadata
├── services/ssoAuthService.js        → SSO authentication logic
├── pages/LoginPageSSO.js             → Dual auth login page
└── pages/auth/SSOCallbackPage.js     → SAML response handler
```

### PHP Backend (Copy to production)
```
php-backend-examples/
├── check-user.php                    → User existence check
├── saml-login.php                    → Initiate SAML auth
├── saml-callback.php                 → Handle SAML response
└── saml-config.php                   → SAML configuration

backend-examples/
├── config/saml-config.php            → Complete SAML config
├── helpers/saml-helpers.php          → Utility functions
├── database-schema.sql               → Database migration
├── composer.json                     → PHP dependencies
├── ADMIN_GUIDE.md                    → How to manage users
├── DEPLOYMENT_GUIDE.md               → How to deploy
└── README.md                         → Overview
```

### Documentation
```
SSO_IMPLEMENTATION.md                 → Technical implementation guide
SSO_IMPLEMENTATION_CHECKLIST.md       → Complete status checklist
```

---

## 🚀 Quick Start Deployment

### 1. Database Setup (5 minutes)
```bash
# Backup first!
mysqldump -u root -p dentwizard_db > backup.sql

# Run migration
mysql -u root -p dentwizard_db < backend-examples/database-schema.sql

# Add test user
mysql -u root -p dentwizard_db
```
```sql
INSERT INTO users (email, first_name, last_name, auth_type, password, budget, is_active, created_at)
VALUES ('test.user@dentwizard.com', 'Test', 'User', 'sso', NULL, 500.00, 1, NOW());
```

### 2. PHP Backend Setup (10 minutes)
```bash
# Install dependencies
composer require onelogin/php-saml

# Copy files to production
cp php-backend-examples/* /lg/API/v1/auth/
cp backend-examples/config/* /lg/API/v1/config/
cp backend-examples/helpers/* /lg/API/v1/helpers/
```

Create `/lg/API/v1/.env`:
```env
DB_HOST=localhost
DB_NAME=dentwizard_db
DB_USER=your_user
DB_PASS=your_password
JWT_SECRET=generate-random-32-byte-string
REACT_APP_URL=https://dentwizardapparel.com
```

### 3. React Frontend (5 minutes)
```bash
# Update .env.production
npm run build

# Deploy build files
cp -r build/* /path/to/webroot/
```

### 4. Azure AD Setup
Email DentWizard IT:
```
Entity ID: https://dentwizardapparel.com
Reply URL: https://dentwizardapparel.com/lg/API/v1/auth/saml/callback
Sign-on URL: https://dentwizardapparel.com/login
```

---

## 👥 User Management Cheat Sheet

### Add New SSO User
```sql
INSERT INTO users (email, first_name, last_name, auth_type, password, budget, is_active, created_at)
VALUES ('john.doe@dentwizard.com', 'John', 'Doe', 'sso', NULL, 500.00, 1, NOW());
```

### Convert Existing User to SSO
```sql
UPDATE users 
SET auth_type = 'sso', password = NULL 
WHERE email = 'user@dentwizard.com';
```

### Deactivate User
```sql
UPDATE users SET is_active = 0 WHERE email = 'user@dentwizard.com';
```

### View All SSO Users
```sql
SELECT email, CONCAT(first_name, ' ', last_name) as name, budget, last_login 
FROM users WHERE auth_type = 'sso' ORDER BY last_login DESC;
```

---

## 🔍 Testing Checklist

### ✅ Standard Login
- [ ] Non-DentWizard email shows password field
- [ ] Correct password logs in successfully
- [ ] Wrong password shows error

### ✅ SSO Login
- [ ] @dentwizard.com email shows Microsoft button
- [ ] Clicking button redirects to Azure AD
- [ ] Azure login works
- [ ] Returns to app authenticated

### ✅ Error Handling
- [ ] User not in DB: "Account not set up"
- [ ] User inactive: "Account deactivated"
- [ ] User not SSO type: "Not configured for SSO"

---

## 🆘 Common Issues & Fixes

### Issue: "Class not found" error
```bash
cd /lg/API/v1
composer install
```

### Issue: "Database connection failed"
Check `.env` file has correct DB credentials

### Issue: "User not found" error
Add user to database first:
```sql
INSERT INTO users (...) VALUES (...);
```

### Issue: SAML validation fails
1. Check certificate in `saml-config.php`
2. Verify Azure AD configuration
3. Check server time is correct (clock skew)

---

## 📊 How It Works

```
Login Page
    ↓
User enters email
    ↓
System detects domain
    ↓
┌─────────────────────┬──────────────────────┐
│   Standard User      │     SSO User         │
│   (any email)        │  (@dentwizard.com)   │
├─────────────────────┼──────────────────────┤
│ Show password field  │ Show Microsoft button│
│        ↓             │         ↓            │
│ Validate password    │  Redirect to Azure   │
│        ↓             │         ↓            │
│  Generate JWT        │  Azure authenticates │
│        ↓             │         ↓            │
│  User logged in      │ Check user in DB     │
│                      │         ↓            │
│                      │  Generate JWT        │
│                      │         ↓            │
│                      │  User logged in      │
└─────────────────────┴──────────────────────┘
```

---

## 📞 Documentation Index

| Document | Purpose | When to Use |
|----------|---------|-------------|
| `DEPLOYMENT_GUIDE.md` | Step-by-step deployment | **Start here** for production setup |
| `ADMIN_GUIDE.md` | User management | Managing SSO users daily |
| `SSO_IMPLEMENTATION.md` | Technical details | Understanding the system |
| `SSO_IMPLEMENTATION_CHECKLIST.md` | Status tracking | Checking what's done |
| `database-schema.sql` | Database changes | Initial setup |
| This file | Quick reference | Daily operations |

---

## 🎯 Pre-Provisioning Model Summary

**Key Concept:** Users MUST exist in database before SSO login

**Why?** 
- ✅ Full control over budgets
- ✅ Explicit approval process
- ✅ No surprise users
- ✅ Clean audit trail

**Workflow:**
1. HR notifies admin of new employee
2. Admin adds user to database with SQL
3. Admin sets budget and permissions
4. Admin notifies user account is ready
5. User logs in via SSO
6. Everything works!

---

## ⚡ Emergency Contacts

**For deployment issues:** Development Team  
**For Azure AD issues:** DentWizard IT Department  
**For user issues:** See ADMIN_GUIDE.md  
**For certificate renewal:** 30 days before 2028-09-10

---

## ✅ Deployment Status

**Code Complete:** ✅ 100%  
**Documentation Complete:** ✅ 100%  
**Ready to Deploy:** ✅ YES  

**Next Step:** Follow `DEPLOYMENT_GUIDE.md` Phase 1

---

_Last Updated: [Current Date]_  
_Implementation Type: Pre-Provisioned Users_  
_SAML Provider: Azure AD_  
_Authentication: SAML 2.0_  
_Token Type: JWT (8-hour expiration)_
