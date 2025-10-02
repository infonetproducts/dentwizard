# Azure AD SSO - Quick Setup Guide

## What We Need From DentWizard IT Team

Since you use Microsoft 365, we'll use Azure AD for employee login. Your IT admin needs to:

### 1. Create App Registration in Azure Portal
- Go to portal.azure.com → Azure Active Directory → App registrations
- Click "New registration"
- Name: "DentWizard Apparel Store"
- Set redirect URL: `https://your-app.onrender.com/auth/callback`

### 2. Generate Client Secret
- In the app registration, go to "Certificates & secrets"
- Create new client secret
- **Copy immediately** (won't show again!)

### 3. Grant Permissions
- Go to "API permissions"
- Add Microsoft Graph permissions: User.Read, email, profile, openid
- Click "Grant admin consent" (important!)

### 4. Send Us These Values
```
Tenant ID: ________________________________
Client ID: ________________________________
Client Secret: ________________________________
Azure Domain: ___________.onmicrosoft.com
```

---

## Timeline
- **Setup in Azure**: 10-15 minutes
- **Integration**: 1-2 days
- **Testing**: 2-3 days
- **Full deployment**: 1 week

## Benefits
✅ Employees use existing Microsoft passwords
✅ No extra licenses needed (included with Office 365)
✅ Automatic access removal when employees leave
✅ Inherits your existing security policies (MFA, etc.)

## Questions?
This is a standard Azure AD integration that doesn't affect any existing systems. Your IT team has likely done this before for other applications.

---

*See AZURE_AD_SSO_SETUP.md for complete technical documentation*