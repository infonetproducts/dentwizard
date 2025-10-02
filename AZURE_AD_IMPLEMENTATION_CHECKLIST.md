# Azure AD SSO Implementation Checklist

## Phase 1: Get Credentials from Client ⏳

### Email to Send Now
```
Subject: Azure AD Credentials Needed for Apparel Store

Hi [Client],

To complete the SSO integration with your Microsoft 365 accounts, I need your IT team to create an App Registration in Azure AD.

I've attached two documents:
- AZURE_AD_QUICK_START.md - Simple 1-page guide for your IT team
- AZURE_AD_SSO_SETUP.md - Complete documentation with all details

The setup takes about 10 minutes. Once complete, please provide:
1. Tenant ID
2. Client ID  
3. Client Secret
4. Your Azure domain (xxx.onmicrosoft.com)

Thanks!
```

### While Waiting
- [ ] Review AZURE_AD_SSO_SETUP.md
- [ ] Set up React project structure for MSAL
- [ ] Prepare .env.example with Azure variables

---

## Phase 2: Initial Setup 🔧

### Once Credentials Received
- [ ] Create `.env` file (never commit!)
```bash
AZURE_TENANT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
AZURE_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
AZURE_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### React App Setup
- [ ] Install MSAL packages
```bash
npm install @azure/msal-browser @azure/msal-react
```

- [ ] Create `src/authConfig.js`
- [ ] Wrap App with MsalProvider
- [ ] Create login/logout components
- [ ] Test login flow locally

### PHP API Setup
- [ ] Install JWT library
```bash
composer require firebase/php-jwt:^5.5
```

- [ ] Update `/v1/auth/validate.php` with Azure AD validation
- [ ] Test token validation with Postman
- [ ] Verify session creation

---

## Phase 3: Integration Testing 🧪

### Local Testing (localhost:3000)
- [ ] Login button triggers Microsoft popup
- [ ] Can enter test account credentials
- [ ] Redirects back to app
- [ ] Token sent to PHP API
- [ ] API validates token successfully
- [ ] User session created
- [ ] Cart persists after login
- [ ] Logout clears session

### API Testing
- [ ] Token validation endpoint works
- [ ] Invalid tokens rejected
- [ ] Expired tokens handled
- [ ] User creation/update in database
- [ ] Budget information loads

### Error Handling
- [ ] Login failure shows error
- [ ] Network errors handled gracefully
- [ ] Session timeout works
- [ ] CORS configured correctly

---

## Phase 4: Staging Deployment 🚀

### Render Configuration
- [ ] Add environment variables in Render dashboard
```
REACT_APP_AZURE_CLIENT_ID=xxx
REACT_APP_AZURE_TENANT_ID=xxx
REACT_APP_REDIRECT_URI=https://your-app.onrender.com/auth/callback
```

- [ ] Update Azure AD with Render URLs
- [ ] Deploy React app to Render
- [ ] Test SSO on Render domain

### PHP API Server
- [ ] Add Azure credentials to production .env
- [ ] Deploy updated PHP API
- [ ] Test token validation on production
- [ ] Verify CORS allows Render domain

---

## Phase 5: User Acceptance Testing 👥

### Test with Client Team
- [ ] Provide test URL to 5-10 users
- [ ] Users can login with work accounts
- [ ] Budgets display correctly
- [ ] Department discounts apply
- [ ] Cart/checkout works
- [ ] Gather feedback

### Issues to Watch For
- [ ] MFA working properly
- [ ] Session duration appropriate
- [ ] Mobile browsers work
- [ ] Different departments access correctly
- [ ] Performance acceptable

---

## Phase 6: Production Launch 🎯

### Pre-Launch (Day Before)
- [ ] All environment variables set
- [ ] Azure AD production URLs configured
- [ ] Rate limiting enabled
- [ ] Logging configured
- [ ] Backup plan ready

### Launch Day
- [ ] Deploy to production
- [ ] Test with admin account
- [ ] Enable for pilot group (IT team)
- [ ] Monitor error logs
- [ ] Check Azure AD sign-in logs

### Post-Launch
- [ ] Monitor first 100 logins
- [ ] Check for authentication errors
- [ ] Review performance metrics
- [ ] Address user feedback
- [ ] Document any issues

---

## Phase 7: Full Rollout 📢

### Communication
- [ ] Email to all employees about new system
- [ ] Include login instructions
- [ ] IT support contact info
- [ ] Training materials if needed

### Monitoring (First Week)
- [ ] Daily login success rate
- [ ] Average session duration
- [ ] Error rate < 1%
- [ ] Response time < 2 seconds
- [ ] User feedback positive

---

## Troubleshooting Guide 🔨

### If Login Fails
```javascript
// Check browser console for:
- MSAL errors
- Network requests to Microsoft
- Redirect URI mismatches
- Token response

// Check PHP logs for:
- Token validation errors
- Database connection issues
- Session problems
```

### Common Fixes
| Issue | Solution |
|-------|----------|
| Redirect URI mismatch | Update in Azure AD exactly |
| Token validation fails | Check server time sync |
| No admin consent | IT admin must grant consent |
| User can't login | Verify in correct AD tenant |
| Session lost | Check cookie settings |

---

## Support Resources 📚

### Documentation
- [Azure AD Docs](https://docs.microsoft.com/azure/active-directory)
- [MSAL.js Guide](https://github.com/AzureAD/microsoft-authentication-library-for-js)
- [JWT Debugger](https://jwt.ms)

### Testing Tools
- Postman - API testing
- jwt.ms - Token inspection  
- Browser DevTools - Network/Console
- Azure Portal - Sign-in logs

---

## Success Criteria ✅

The integration is complete when:
- [ ] All 3,000 users can login
- [ ] Login success rate > 99%
- [ ] No critical bugs in production
- [ ] IT team trained on management
- [ ] Documentation complete
- [ ] Monitoring in place

---

## Notes Section 📝

```
Client IT Contact: ___________________
Azure Admin: ________________________
Test Account: _______________________
Go-Live Date: ______________________
```

---

*Use this checklist to track progress. Check off items as completed.*
*Keep this document updated throughout the implementation.*