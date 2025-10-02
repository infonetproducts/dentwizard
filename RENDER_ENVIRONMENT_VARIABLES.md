# Environment Variables for Render Deployment
# Copy these values into Render Dashboard

## STAGING Environment
Service: dentwizard-app-staging
Environment Variables to Set:
- REACT_APP_API_URL = https://your-ec2-api-url.com (your AWS EC2 API URL)
- REACT_APP_ENVIRONMENT = staging

Expected URLs after deployment:
- Frontend: https://dentwizard-app-staging.onrender.com
- API: Your existing AWS EC2 URL

---

## PRODUCTION Environment
Service: dentwizard-app
Environment Variables to Set:
- REACT_APP_API_URL = https://your-ec2-api-url.com (your AWS EC2 API URL)
- REACT_APP_ENVIRONMENT = production

Expected URLs after deployment:
- Frontend: https://dentwizard-app.onrender.com
- API: Your existing AWS EC2 URL

---

## Important Notes:

1. **REACT_APP_API_URL should be your AWS EC2 API URL**
   Example: https://api.dentwizard.com
   Or: https://ec2-xx-xxx-xxx-xxx.compute-1.amazonaws.com

2. **CORS Configuration Required on AWS EC2**
   You MUST add these origins to your EC2 API's CORS settings:
   - https://dentwizard-app-staging.onrender.com (for staging)
   - https://dentwizard-app.onrender.com (for production)

3. **SSL/HTTPS**
   Render provides free SSL - both URLs will be HTTPS automatically

4. **Custom Domain (Optional)**
   You can add custom domains like:
   - staging.dentwizard.com → dentwizard-app-staging
   - app.dentwizard.com → dentwizard-app
