# DentWizard Apparel SSO Backend

Complete SAML 2.0 SSO integration for DentWizard Apparel e-commerce platform.

## 📋 Overview

This directory contains all backend PHP code and documentation for implementing SSO (Single Sign-On) with Azure AD. The implementation uses a **pre-provisioning model** where users must exist in the database before they can log in via SSO.

### Key Features

- ✅ **Dual Authentication** - Supports both SSO (@dentwizard.com) and standard email/password login
- ✅ **Pre-Provisioning** - Full admin control over user budgets before first login
- ✅ **Backward Compatible** - Existing PHP users continue working without changes
- ✅ **Secure** - SAML signature validation, JWT tokens, audit logging
- ✅ **Production Ready** - Complete with error handling, logging, and monitoring

---

## 📁 Directory Structure

```
backend-examples/
├── config/
│   └── saml-config.php              # Complete Azure AD SAML configuration
│
├── helpers/
│   └── saml-helpers.php             # Utility functions for SAML, JWT, database
│
├── ADMIN_GUIDE.md                   # How to add/manage SSO users
├── DEPLOYMENT_GUIDE.md              # Step-by-step production deployment
├── QUICK_REFERENCE.md               # Quick start and cheat sheets
├── README.md                        # This file
├── composer.json                    # PHP dependencies (onelogin/php-saml)
└── database-schema.sql              # Database migration script

../php-backend-examples/             # Files to copy to production
├── check-user.php                   # Check if user exists and auth type
├── saml-login.php                   # Initiate SAML authentication
├── saml-callback.php                # Handle SAML response from Azure AD
└── saml-config.php                  # SAML configuration (copy to config/)
```

---

## 🚀 Quick Start

### 1. Read Documentation First

Start here (in order):
1. **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** - Overview and quick commands
2. **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Step-by-step deployment
3. **[ADMIN_GUIDE.md](ADMIN_GUIDE.md)** - Day-to-day user management

### 2. Install Dependencies

```bash
composer require onelogin/php-saml
```

### 3. Database Setup

```bash
mysql -u root -p dentwizard_db < database-schema.sql
```

### 4. Add Test User

```sql
INSERT INTO users (email, first_name, last_name, auth_type, password, budget, is_active, created_at)
VALUES ('test.user@dentwizard.com', 'Test', 'User', 'sso', NULL, 500.00, 1, NOW());
```

### 5. Deploy Backend Files

Copy to your production server:
```
/lg/API/v1/
├── auth/
│   ├── check-user.php
│   └── saml/
│       ├── login.php
│       ├── callback.php
│       └── logout.php
├── config/
│   └── saml-config.php
└── helpers/
    └── saml-helpers.php
```

### 6. Configure Environment

Create `/lg/API/v1/.env`:
```env
DB_HOST=localhost
DB_NAME=dentwizard_db
DB_USER=your_db_user
DB_PASS=your_db_password
JWT_SECRET=your-32-byte-random-string
REACT_APP_URL=https://dentwizardapparel.com
```

### 7. Test

See **Testing Checklist** in [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md#phase-5-testing)

---

## 📖 Documentation Guide

### For Deployment
👉 **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Complete step-by-step instructions
- Prerequisites
- Database setup
- File deployment
- Testing procedures
- Troubleshooting
- Rollback plan

### For User Management
👉 **[ADMIN_GUIDE.md](ADMIN_GUIDE.md)** - How to manage SSO users
- Add new SSO users
- Convert existing users to SSO
- Deactivate users
- Update budgets
- SQL queries for reporting
- Troubleshooting user issues

### For Daily Operations
👉 **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** - Quick commands and cheat sheets
- Common SQL queries
- File locations
- Testing checklist
- Common issues & fixes

### For Technical Understanding
👉 **../SSO_IMPLEMENTATION.md** - Detailed technical documentation
- Architecture overview
- Authentication flow
- Security considerations
- API specifications

### For Project Status
👉 **../SSO_IMPLEMENTATION_CHECKLIST.md** - Master checklist
- What's complete
- What's pending
- Testing status
- Deployment checklist

---

## 🔑 How Pre-Provisioning Works

### The Model

Users **MUST exist in database BEFORE** they can log in via SSO.

### The Workflow

```
1. DentWizard hires new employee
   └─> "john.doe@dentwizard.com"

2. HR notifies your admin
   └─> "Please create account for John Doe"

3. Admin adds user to database
   └─> INSERT INTO users (email, auth_type, budget...) VALUES (...)

4. Admin sets budget ($500)
   └─> User has budget before first login

5. Admin notifies user
   └─> "Your account is ready"

6. User goes to login page
   └─> Enters john.doe@dentwizard.com

7. System detects @dentwizard.com
   └─> Shows "Sign in with Microsoft" button

8. User clicks button
   └─> Redirects to Azure AD

9. User authenticates with Microsoft
   └─> Azure validates credentials

10. Azure sends SAML response back
    └─> PHP callback validates response

11. PHP checks: Is user in database?
    ├─> YES: Generate JWT token → Success! ✅
    └─> NO: Show error → "Contact administrator" ❌

12. User is authenticated
    └─> Can shop with their $500 budget
```

### Why Pre-Provisioning?

**Advantages:**
- ✅ **Budget Control** - Set budgets before users can spend
- ✅ **Approval Process** - Explicit authorization required
- ✅ **No Surprises** - No automatic user creation
- ✅ **Clean Audit** - Clear record of who authorized what

**Alternative (Not Used):**
- ❌ **Auto-Provisioning (JIT)** - Users auto-created on first login
  - Less control over budgets
  - Anyone with @dentwizard.com can login
  - May require cleanup later

---

## 🔐 Security Features

### SAML Validation
- ✅ Signature verification using Azure AD certificate
- ✅ Timestamp validation (NotBefore/NotOnOrAfter)
- ✅ Audience restriction check
- ✅ Issuer validation

### Token Security
- ✅ JWT tokens with 8-hour expiration
- ✅ Signed with secret key
- ✅ Contains only necessary user data
- ✅ Validated on every API request

### Audit Logging
- ✅ All SSO login attempts logged
- ✅ IP address tracking
- ✅ Success/failure recording
- ✅ Error message capture

### Access Control
- ✅ User must exist in database
- ✅ User must be SSO-enabled (`auth_type = 'sso'`)
- ✅ User must be active (`is_active = 1`)
- ✅ Budget limits enforced

---

## 🛠️ Technology Stack

### Required
- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- **Composer** - PHP package manager
- **onelogin/php-saml** - SAML library

### Optional
- **PDO** - Database abstraction (recommended)
- **SSL/HTTPS** - Required for production

### Azure AD
- **SAML 2.0** protocol
- **Tenant ID**: `ea1c5a3f-4d62-491a-8ba4-2e9955015493`
- **Certificate Expiration**: September 10, 2028

---

## 📊 Database Schema

### New Columns Added to `users` Table

```sql
auth_type ENUM('standard', 'sso') DEFAULT 'standard'
  -- Determines which authentication method user uses

azure_ad_object_id VARCHAR(255) NULL
  -- Azure's internal user ID (auto-populated)

last_login DATETIME NULL
  -- Track when user last logged in

password VARCHAR(255) NULL
  -- Made nullable (SSO users don't need password)
```

### New Table: `sso_audit_log`

```sql
id INT AUTO_INCREMENT PRIMARY KEY
email VARCHAR(255)             -- User attempting login
success BOOLEAN                -- Did login succeed?
error_message TEXT             -- Error if failed
ip_address VARCHAR(45)         -- IP address of attempt
user_agent TEXT                -- Browser/device info
created_at DATETIME            -- When attempt occurred
```

---

## 🧪 Testing

### Test Scenarios

1. **Standard User Login** (existing users)
   - Should work exactly as before
   - No changes to existing functionality

2. **SSO User Login** (new @dentwizard.com users)
   - Email detection triggers SSO
   - Microsoft button appears
   - Azure AD authentication
   - Successful callback and login

3. **Error Cases**
   - User not in database
   - User inactive
   - User wrong auth type
   - Invalid SAML response

See complete testing checklist in [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md#phase-5-testing)

---

## 🆘 Troubleshooting

### Common Issues

**"Class not found" error**
```bash
composer install  # Install PHP dependencies
```

**"Database connection failed"**
- Check `.env` file has correct credentials
- Test MySQL connection manually

**"User not found" error**
- User must be added to database first
- Use SQL INSERT or admin panel

**SAML validation fails**
- Check certificate in `saml-config.php`
- Verify Azure AD configuration
- Check server time (clock skew)

**Redirect not working**
- Verify callback URL matches in Azure AD
- Check React app URL in `.env`

See complete troubleshooting in [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md#troubleshooting)

---

## 📞 Support

### Documentation
- **Deployment**: [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)
- **User Management**: [ADMIN_GUIDE.md](ADMIN_GUIDE.md)
- **Quick Reference**: [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- **Technical Details**: ../SSO_IMPLEMENTATION.md

### Logs to Check
- PHP error logs: `/var/log/php_errors.log`
- Audit logs: `SELECT * FROM sso_audit_log ORDER BY created_at DESC;`
- Apache/Nginx logs

### External Resources
- OneLogin PHP SAML: https://github.com/onelogin/php-saml
- Azure AD SAML: https://docs.microsoft.com/azure/active-directory/develop/

---

## 🎯 Deployment Checklist

Before going to production:

- [ ] Database backed up
- [ ] Database schema migrated
- [ ] Test user created
- [ ] Composer dependencies installed
- [ ] PHP files deployed to correct locations
- [ ] Environment variables configured
- [ ] File permissions set correctly
- [ ] SAML configuration updated for production
- [ ] Azure AD configuration provided to DentWizard
- [ ] All tests passing
- [ ] Monitoring/logging enabled
- [ ] Certificate expiration reminder set

See complete checklist in [../SSO_IMPLEMENTATION_CHECKLIST.md](../SSO_IMPLEMENTATION_CHECKLIST.md)

---

## 📅 Maintenance

### Regular Tasks
- Monthly user audit
- Review failed login attempts
- Monitor disk space for audit logs
- Verify backups are current

### Important Dates
- **Certificate Expires**: September 10, 2028
  - Set reminders for August 10 and September 1, 2028
  - Contact DentWizard IT for certificate renewal

### Updates
- Keep onelogin/php-saml library updated
- Review Azure AD security advisories
- Update documentation as system evolves

---

## 📄 License & Credits

**Implementation**: Custom for DentWizard Apparel  
**SAML Library**: OneLogin PHP SAML (MIT License)  
**Authentication Provider**: Microsoft Azure AD  

---

## ✅ Ready to Deploy

All code is complete and production-ready!

**Next Step**: Open [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) and follow Phase 1

---

_Last Updated: [Current Date]_  
_Version: 1.0_  
_Status: Production Ready ✅_
