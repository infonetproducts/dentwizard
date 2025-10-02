# DentWizard Render Deployment Guide
**Frontend Deployment to Render.com with AWS Backend**

---

## 🎯 Architecture Overview

```
Render.com (Frontend)          AWS (Backend)
├─ Staging                     ├─ EC2 (PHP API)
│  └─ React App                └─ RDS (MySQL)
└─ Production
   └─ React App
```

---

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ GitHub account with dentwizard repository
- ✅ AWS EC2 API URL
- ✅ Access to AWS EC2 to configure CORS
- ✅ Credit card (for Render account - free tier available)

---

## 🚀 Part 1: Create Render Account

### Step 1: Sign Up
1. Go to: https://render.com
2. Click **"Get Started"**
3. Sign up with your **GitHub account** (recommended)
4. Authorize Render to access your GitHub repositories

### Step 2: Verify Email
1. Check your email for verification link
2. Complete email verification

---

## 🏗️ Part 2: Deploy Staging Environment

### Step 1: Create Staging Service
1. In Render Dashboard, click **"New +"**
2. Select **"Web Service"**
3. Connect your repository:
   - Select **"infonetproducts/dentwizard"**
   - Click **"Connect"**

### Step 2: Configure Staging Service
Fill in these settings:

**Basic Settings:**
- **Name:** `dentwizard-app-staging`
- **Region:** Oregon (US West)
- **Branch:** `main` (or create `staging` branch)
- **Root Directory:** (leave blank)
- **Runtime:** Static Site

**Build Settings:**
- **Build Command:** 
  ```bash
  cd react-app && npm install && npm run build
  ```
- **Publish Directory:** 
  ```
  react-app/build
  ```

**Plan:**
- Select **"Free"** (for staging)

### Step 3: Set Environment Variables
Click **"Advanced"** → **"Environment Variables"**

Add these variables:
```
REACT_APP_API_URL = YOUR_AWS_EC2_API_URL
REACT_APP_ENVIRONMENT = staging
```

**Important:** Replace `YOUR_AWS_EC2_API_URL` with your actual EC2 API endpoint
Example: `https://api.dentwizard.com` or `https://ec2-xx-xxx.compute.amazonaws.com`

### Step 4: Deploy
1. Click **"Create Web Service"**
2. Wait for deployment (5-10 minutes)
3. Note your staging URL: `https://dentwizard-app-staging.onrender.com`

---

## 🏭 Part 3: Deploy Production Environment

### Step 1: Create Production Service
1. In Render Dashboard, click **"New +"**
2. Select **"Web Service"**
3. Connect repository: **"infonetproducts/dentwizard"**

### Step 2: Configure Production Service
Fill in these settings:

**Basic Settings:**
- **Name:** `dentwizard-app`
- **Region:** Oregon (US West)
- **Branch:** `main`
- **Root Directory:** (leave blank)
- **Runtime:** Static Site

**Build Settings:**
- **Build Command:** 
  ```bash
  cd react-app && npm install && npm run build
  ```
- **Publish Directory:** 
  ```
  react-app/build
  ```

**Plan:**
- Select **"Starter"** ($7/month) - Includes custom domain support

### Step 3: Set Environment Variables
Add these variables:
```
REACT_APP_API_URL = YOUR_AWS_EC2_API_URL
REACT_APP_ENVIRONMENT = production
```

### Step 4: Deploy
1. Click **"Create Web Service"**
2. Wait for deployment (5-10 minutes)
3. Note your production URL: `https://dentwizard-app.onrender.com`

---

## 🔧 Part 4: Configure AWS EC2 CORS

**CRITICAL:** Your EC2 API must allow requests from Render URLs.

### Step 1: SSH into EC2
```bash
ssh -i your-key.pem ec2-user@your-ec2-ip
```

### Step 2: Update CORS Configuration

Find your CORS configuration file (usually in your API directory):

**For PHP (cors.php or in .htaccess):**
```php
<?php
header("Access-Control-Allow-Origin: https://dentwizard-app-staging.onrender.com");
header("Access-Control-Allow-Origin: https://dentwizard-app.onrender.com");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
?>
```

**Or update your existing CORS config to include:**
```php
$allowed_origins = [
    'https://dentwizard.lgstore.com',  // Your existing domain
    'https://dentwizard-app-staging.onrender.com',  // Staging
    'https://dentwizard-app.onrender.com',  // Production
    'http://localhost:3000'  // Local development
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
```

### Step 3: Restart Web Server
```bash
sudo systemctl restart apache2
# OR
sudo systemctl restart nginx
```

---

## ✅ Part 5: Test Your Deployment

### Test Staging
1. Visit: `https://dentwizard-app-staging.onrender.com`
2. Try logging in
3. Test product browsing
4. Test cart functionality
5. Check browser console for errors

### Test Production
1. Visit: `https://dentwizard-app.onrender.com`
2. Repeat all tests from staging
3. Verify everything works correctly

---

## 🌐 Part 6: Custom Domain Setup (Optional)

### If You Have a Custom Domain:

**For Staging:**
1. In Render dashboard, go to your staging service
2. Click **"Settings"** → **"Custom Domain"**
3. Add: `staging.dentwizard.com`
4. Add CNAME record in your DNS:
   ```
   staging.dentwizard.com → dentwizard-app-staging.onrender.com
   ```

**For Production:**
1. In Render dashboard, go to your production service
2. Click **"Settings"** → **"Custom Domain"**
3. Add: `app.dentwizard.com` or `www.dentwizard.com`
4. Add CNAME record in your DNS:
   ```
   app.dentwizard.com → dentwizard-app.onrender.com
   ```

5. **Update CORS on EC2** to include custom domains

---

## 🔄 Part 7: Continuous Deployment

### Automatic Deployments
Render automatically deploys when you push to GitHub:

1. **Make changes** to your code locally
2. **Commit and push** to GitHub:
   ```bash
   git add .
   git commit -m "Update feature"
   git push origin main
   ```
3. **Render auto-deploys** within 2-5 minutes

### Manual Deployments
In Render dashboard:
1. Go to your service
2. Click **"Manual Deploy"** → **"Deploy latest commit"**

---

## 💰 Cost Breakdown

### Staging Environment
- **Static Site:** FREE (Render free tier)
- **Total:** $0/month

### Production Environment
- **Static Site:** $7/month (Starter plan)
- **Total:** $7/month

### Total Monthly Cost
**$7/month** for both staging and production

*Your AWS costs (EC2 + RDS) remain unchanged*

---

## 🐛 Troubleshooting

### Issue: "Failed to fetch" or CORS errors
**Solution:** Check CORS configuration on EC2. Ensure Render URLs are in allowed origins.

### Issue: Build fails on Render
**Solution:** Check build logs in Render dashboard. Common issues:
- Missing dependencies in package.json
- Build command incorrect
- Environment variables not set

### Issue: White screen or 404 errors
**Solution:** 
- Check that publish directory is `react-app/build`
- Verify routes are configured with rewrite rule
- Check browser console for errors

### Issue: API calls fail
**Solution:**
- Verify `REACT_APP_API_URL` is set correctly
- Check API is accessible from Render (not IP-restricted)
- Verify CORS headers on EC2

---

## 📞 Support

If you encounter issues:
1. Check Render logs in dashboard
2. Check browser console (F12)
3. Check EC2 API logs
4. Render Support: https://render.com/docs

---

## ✅ Checklist

Before going live, ensure:
- [ ] Staging deployed and tested
- [ ] Production deployed and tested
- [ ] CORS configured on EC2
- [ ] All features work (login, cart, checkout)
- [ ] Environment variables set correctly
- [ ] Custom domain configured (if applicable)
- [ ] SSL certificates active (automatic on Render)
- [ ] GitHub auto-deploy working

---

**Deployment Complete! 🎉**

Your React frontend is now live on Render, connected to your AWS backend!
