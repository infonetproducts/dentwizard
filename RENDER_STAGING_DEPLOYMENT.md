# Render Staging Deployment Guide

## ✅ Git Branches Created Successfully!

Your repository now has two branches:
- **staging** → Deploys to https://dentwizard.onrender.com
- **master** → Deploys to https://dentwizard-prod.onrender.com

---

## 🚀 Next Steps to Complete Staging Deployment

### Step 1: Configure Render Service

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Find your service**: `dentwizard-stage` (Service ID: srv-d3fcekffte5s73a1bgug)
3. Click on the service to open it

### Step 2: Verify Settings

Check that these settings are configured correctly:

#### Build & Deploy Settings:
- **Branch**: `staging` ✅ (should auto-detect the new branch)
- **Build Command**: `cd react-app && npm install && REACT_APP_API_URL=$REACT_APP_API_URL npm run build`
- **Publish Directory**: `./react-app/build`

#### Environment Variables:
Add these in the Render dashboard:

| Variable | Value |
|----------|-------|
| `REACT_APP_API_URL` | `https://dentwizard.lgstore.com` |
| `REACT_APP_ENVIRONMENT` | `staging` |

### Step 3: Trigger Deployment

**Option A: Automatic Deployment** (if auto-deploy is enabled)
- Render should automatically detect the new staging branch and start deploying

**Option B: Manual Deployment**
1. In the Render dashboard, click "Manual Deploy"
2. Select "Deploy latest commit"
3. Click "Deploy"

---

## 🔍 Monitor Deployment

### Watch the Build Logs:
1. In Render dashboard → Your service → "Logs" tab
2. You'll see:
   - Installing dependencies
   - Building React app
   - Deploying to staging

### Expected Build Time: 3-5 minutes

---

## ✅ Testing After Deployment

Once deployed, test your staging app:

### 1. Access Staging URL:
**https://dentwizard.onrender.com**

### 2. Test These Features:
- ✅ Login with your credentials (jkrugger@infonetproducts.com / password)
- ✅ Browse products
- ✅ Add items to cart
- ✅ View your profile, addresses, orders
- ✅ Check that API calls work (Chrome DevTools → Network tab)

### 3. Verify CORS Headers:
Open Chrome DevTools → Network tab → Check any API call:
- Should see: `access-control-allow-origin: https://dentwizard.onrender.com`
- No CORS errors in console

---

## 🎯 Your Deployment Workflow

### For Testing (Staging):
```bash
# Make changes to your code
git add .
git commit -m "Your changes"
git push origin staging
# Render auto-deploys to https://dentwizard.onrender.com
# Test thoroughly on staging
```

### For Production (After Staging Tests Pass):
```bash
# Switch to master branch
git checkout master

# Merge staging into master
git merge staging

# Push to master
git push origin master

# Render auto-deploys to https://dentwizard-prod.onrender.com
```

---

## 🌐 Complete Environment Map

| Environment | Branch | Render Service | URL | API |
|-------------|--------|----------------|-----|-----|
| **Local** | - | - | http://localhost:3000 | AWS EC2 |
| **Staging** | staging | dentwizard-stage | https://dentwizard.onrender.com | AWS EC2 |
| **Production** | master | dentwizard-prod | https://dentwizard-prod.onrender.com | AWS EC2 |
| **Custom Domain** | master | dentwizard-prod | https://dentwizardapparel.com | AWS EC2 |

### All Environments Use:
- **Same API Server**: AWS EC2 at https://dentwizard.lgstore.com
- **Same Database**: Your AWS RDS MySQL database
- **CORS Configured**: All domains are whitelisted in the API

---

## 🔧 Troubleshooting

### If Render Doesn't Detect Staging Branch:
1. Go to Render Dashboard → Your Service
2. Click "Settings"
3. Under "Branch", manually select `staging`
4. Save and trigger manual deploy

### If Build Fails:
Check the build logs for errors:
- **Missing dependencies**: Ensure package.json is correct
- **Build errors**: Check React app for syntax errors
- **Environment variables**: Verify they're set in Render dashboard

### If API Calls Fail:
1. Check Chrome DevTools console for CORS errors
2. Verify environment variable: `REACT_APP_API_URL=https://dentwizard.lgstore.com`
3. Check that AWS EC2 files were uploaded (we did this earlier)

---

## 📝 Important Notes

### AWS EC2 API Status:
✅ **Already Configured** - Your API is ready and supports:
- http://localhost:3000 (local development)
- https://dentwizard.onrender.com (staging)
- https://dentwizard-prod.onrender.com (production)
- https://dentwizardapparel.com (custom domain)

### Database:
✅ **Shared Database** - All environments use the same AWS RDS database
- This means staging and production share the same users, products, orders
- Be careful when testing order creation or data modifications on staging

### Security:
✅ **CORS Properly Configured** - Only your approved domains can access the API
✅ **Session Cookies Working** - Authentication is secure
✅ **HTTPS Enabled** - All Render deployments use SSL

---

## 🎉 Next Steps

1. **Go to Render Dashboard** and verify staging service is deploying
2. **Wait 3-5 minutes** for build to complete
3. **Test staging app** at https://dentwizard.onrender.com
4. **Report any issues** so we can fix them before production
5. **Once staging works perfectly**, merge to master for production

---

## Need Help?

If you encounter any issues:
- Check Render build logs first
- Verify environment variables are set
- Test API directly: https://dentwizard.lgstore.com/API/v1/user/profile.php
- Check Chrome DevTools console and network tab

**Current Status**: ✅ Staging branch created and pushed to GitHub
**Next**: Wait for Render to detect and deploy the staging branch
