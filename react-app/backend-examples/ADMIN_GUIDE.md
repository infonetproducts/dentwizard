# SSO User Management Guide for Administrators

## Overview

This guide explains how to create and manage SSO users in the DentWizard Apparel system. **Users MUST be created in the database before they can log in via SSO.**

---

## 🎯 Important Concepts

### Authentication Types

Your system supports TWO types of users:

1. **Standard Users** (`auth_type = 'standard'`)
   - Regular PHP users
   - Login with email + password
   - Password stored in database

2. **SSO Users** (`auth_type = 'sso'`)
   - DentWizard employees with @dentwizard.com email
   - Login via Microsoft/Azure AD
   - NO password in database (Azure AD handles authentication)

### Key Fields

- **email**: Must match exactly with Azure AD email (case-insensitive)
- **auth_type**: Set to `'sso'` for SSO users
- **password**: Must be `NULL` for SSO users
- **azure_ad_object_id**: Auto-populated on first login
- **is_active**: Must be `1` (true) for user to log in

---

## 📝 How to Add a New SSO User

### Step 1: Get User Information

Request from DentWizard HR or the employee:
- ✅ Full name (First and Last)
- ✅ Email address (@dentwizard.com)
- ✅ Budget amount
- ✅ Department (optional)

### Step 2: Add User to Database

**Option A: Using PHP Admin Panel** (if you have one)
```
1. Navigate to Users → Add New User
2. Enter email: john.doe@dentwizard.com
3. Enter first name: John
4. Enter last name: Doe
5. Set Auth Type: SSO
6. Set budget: $500.00
7. Leave password field EMPTY
8. Set status: Active
9. Click Save
```

**Option B: Using SQL** (direct database access)
```sql
INSERT INTO users (
    email,
    first_name,
    last_name,
    auth_type,
    password,
    budget,
    department,
    is_active,
    created_at
) VALUES (
    'john.doe@dentwizard.com',  -- Azure AD email
    'John',                      -- First name
    'Doe',                       -- Last name
    'sso',                       -- MUST be 'sso'
    NULL,                        -- MUST be NULL
    500.00,                      -- Budget
    'IT',                        -- Department
    1,                           -- Active
    NOW()                        -- Created timestamp
);
```

### Step 3: Notify User

Send email to the user:
```
Subject: Your DentWizard Apparel Account is Ready

Hi John,

Your account has been created for the DentWizard Apparel store.

Email: john.doe@dentwizard.com
Budget: $500.00

To log in:
1. Go to https://dentwizardapparel.com/login
2. Enter your @dentwizard.com email
3. Click "Sign in with Microsoft"
4. Use your DentWizard Microsoft credentials

Questions? Contact IT support.
```

---

## 🔄 How to Convert Existing User to SSO

If someone already has a standard account and needs to switch to SSO:

```sql
UPDATE users 
SET 
    auth_type = 'sso',
    password = NULL        -- Clear their old password
WHERE email = 'existing.user@dentwizard.com';
```

**Important:** 
- User will NO LONGER be able to use their old password
- They MUST use SSO "Sign in with Microsoft" button
- Their budget and other data remains unchanged

---

## 🚫 How to Deactivate an SSO User

When an employee leaves DentWizard:

```sql
UPDATE users 
SET is_active = 0
WHERE email = 'departed.user@dentwizard.com';
```

**What happens:**
- User can no longer log in (SSO or standard)
- Their data remains in database for records
- Can be reactivated later if needed

To reactivate:
```sql
UPDATE users 
SET is_active = 1
WHERE email = 'user@dentwizard.com';
```

---

## 💰 How to Update User Budget

```sql
UPDATE users 
SET budget = 750.00
WHERE email = 'john.doe@dentwizard.com';
```

Or increase budget by amount:
```sql
UPDATE users 
SET budget = budget + 250.00
WHERE email = 'john.doe@dentwizard.com';
```

---

## 📊 Useful Queries for Admins

### View All SSO Users
```sql
SELECT 
    user_id,
    email,
    CONCAT(first_name, ' ', last_name) as name,
    auth_type,
    budget,
    is_active,
    last_login,
    created_at
FROM users
WHERE auth_type = 'sso'
ORDER BY last_login DESC;
```

### Find Inactive SSO Users
```sql
SELECT 
    email,
    CONCAT(first_name, ' ', last_name) as name,
    last_login
FROM users
WHERE auth_type = 'sso' 
AND is_active = 1
AND (last_login IS NULL OR last_login < DATE_SUB(NOW(), INTERVAL 90 DAY))
ORDER BY last_login ASC;
```

### View Recent SSO Login Attempts
```sql
SELECT 
    email,
    success,
    error_message,
    ip_address,
    created_at
FROM sso_audit_log
ORDER BY created_at DESC
LIMIT 50;
```

### View Failed SSO Logins
```sql
SELECT 
    email,
    error_message,
    COUNT(*) as attempts,
    MAX(created_at) as last_attempt
FROM sso_audit_log
WHERE success = 0
GROUP BY email, error_message
ORDER BY last_attempt DESC;
```

### Total Budget by Auth Type
```sql
SELECT 
    auth_type,
    COUNT(*) as user_count,
    SUM(budget) as total_budget,
    AVG(budget) as avg_budget
FROM users
WHERE is_active = 1
GROUP BY auth_type;
```

---

## 🔍 Troubleshooting Common Issues

### Issue: User says "Sign in with Microsoft" button doesn't appear

**Solution:** Check if email ends with @dentwizard.com
- Button only appears for @dentwizard.com emails
- User must type full email first
- System auto-detects auth type

---

### Issue: User gets "Account not set up" error

**Cause:** User doesn't exist in database

**Solution:** 
1. Verify user's email spelling
2. Check if user exists: `SELECT * FROM users WHERE email = 'their.email@dentwizard.com'`
3. If not found, add user following "How to Add SSO User" steps above

---

### Issue: User gets "Not configured for SSO" error

**Cause:** User exists but `auth_type` is not set to 'sso'

**Solution:**
```sql
UPDATE users 
SET auth_type = 'sso', password = NULL
WHERE email = 'user@dentwizard.com';
```

---

### Issue: User gets "Account deactivated" error

**Cause:** User's `is_active` field is set to 0

**Solution:**
```sql
UPDATE users 
SET is_active = 1
WHERE email = 'user@dentwizard.com';
```

---

### Issue: User successfully logs in but has wrong budget

**Cause:** Budget not set correctly in database

**Solution:**
```sql
UPDATE users 
SET budget = 500.00  -- Correct amount
WHERE email = 'user@dentwizard.com';
```

User must log out and back in to see updated budget.

---

## 📋 Pre-Launch Checklist

Before enabling SSO for production:

- [ ] Database schema updated with SSO columns
- [ ] Test user created in database
- [ ] Test user can log in via SSO
- [ ] Budget displays correctly after SSO login
- [ ] Failed login shows proper error messages
- [ ] Deactivated user cannot log in
- [ ] Audit log is recording attempts
- [ ] Admin queries work for reporting

---

## 🔐 Security Best Practices

1. **Regular Audits**: Review SSO users monthly
   ```sql
   SELECT email, last_login FROM users WHERE auth_type = 'sso' ORDER BY last_login;
   ```

2. **Deactivate Promptly**: When employee leaves, deactivate immediately
   
3. **Monitor Failed Logins**: Check audit log for suspicious activity

4. **Budget Controls**: Set appropriate default budgets per department

5. **Backup Database**: Before bulk changes, backup users table

---

## 📞 Getting Help

**For technical SSO issues:**
- Check `sso_audit_log` table for error messages
- Review PHP error logs
- Contact development team

**For user account issues:**
- Verify user data in database
- Check if user is active
- Confirm email spelling matches Azure AD

**For Azure AD configuration:**
- Contact DentWizard IT department
- They manage Azure AD users and permissions
