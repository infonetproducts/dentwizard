# 🚀 Quick Start Guide - DentWizard React App

## ⚡ Fastest Way to Start (Windows)

1. **Setup Environment Variables**
   - Copy `.env.example` to `.env`
   - Update with your Azure AD credentials:
     ```
     REACT_APP_CLIENT_ID=your_azure_client_id
     REACT_APP_TENANT_ID=your_azure_tenant_id
     REACT_APP_API_URL=http://localhost:8000/api
     ```

2. **Start Development Server**
   - Double-click `start-dev.bat`
   - OR run: `npm install && npm start`

3. **Access Application**
   - Opens automatically at http://localhost:3000
   - Login with your Azure AD credentials

## 📁 Project Status

### ✅ Completed Features
- **Authentication**: Azure AD SSO with MSAL
- **Core Pages**: 
  - Login with SSO
  - Home page with featured products
  - Products catalog with filtering
  - Product details
  - Shopping cart
  - Multi-step checkout
  - User profile management
  - Order history tracking
- **Mobile Optimization**: Fully responsive design
- **State Management**: Redux for cart and user state
- **API Integration**: Axios with interceptors

### 🔧 Prerequisites Check
- [ ] Node.js 14+ installed
- [ ] NPM or Yarn installed
- [ ] Azure AD app configured
- [ ] Backend API running on port 8000
- [ ] `.env` file created with proper values

## 🎯 Next Steps

### Immediate Actions
1. **Install Dependencies**
   ```bash
   cd react-app
   npm install
   ```

2. **Configure Azure AD**
   - Update `.env` with your Azure AD credentials
   - Ensure redirect URI is set to http://localhost:3000

3. **Start Backend API**
   ```bash
   cd ../API
   php -S localhost:8000
   ```

4. **Run React App**
   ```bash
   npm start
   ```

### Testing the Application
1. **Login Flow**
   - Click "Sign in with Microsoft"
   - Enter corporate credentials
   - Should redirect to home page

2. **Shopping Flow**
   - Browse products
   - Add items to cart
   - Proceed to checkout
   - Complete order

3. **Mobile Testing**
   - Open Chrome DevTools (F12)
   - Toggle device toolbar (Ctrl+Shift+M)
   - Test on various screen sizes

## 🐛 Common Issues & Solutions

### Issue: Login redirects fail
**Solution**: Check redirect URI in Azure AD matches exactly

### Issue: API calls fail
**Solution**: Ensure backend is running on port 8000

### Issue: Blank page after login
**Solution**: Check browser console for errors, verify .env values

### Issue: npm install fails
**Solution**: 
```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

## 📱 Mobile-First Features
- **Responsive Grid**: Adapts to all screens
- **Touch Gestures**: Swipe cart drawer
- **Mobile Nav**: Bottom navigation on mobile
- **Optimized Images**: Lazy loading
- **PWA Ready**: Installable as app

## 🚀 Production Deployment

### Build for Production
```bash
# Run build script
build-prod.bat

# OR manually
npm run build
```

### Deploy to Azure
```bash
# Install Azure CLI
npm install -g @azure/static-web-apps-cli

# Deploy
swa deploy ./build --deployment-token <your-token>
```

## 📊 Project Structure Summary
```
react-app/
├── src/
│   ├── pages/           ✅ All pages complete
│   ├── components/      ✅ Layout & auth components
│   ├── store/           ✅ Redux store configured
│   ├── services/        ✅ API service ready
│   └── theme/           ✅ Material-UI theme
├── public/              ✅ Static assets
├── .env.example         ✅ Environment template
├── package.json         ✅ Dependencies defined
├── start-dev.bat        ✅ Quick start script
└── build-prod.bat       ✅ Production build script
```

## 💡 Development Tips
1. **Hot Reload**: Changes auto-refresh in browser
2. **Redux DevTools**: Install Chrome extension for debugging
3. **Network Tab**: Monitor API calls in DevTools
4. **React DevTools**: Inspect component tree

## 📞 Need Help?
- Check `README.md` for detailed documentation
- Review API documentation in `../API/`
- Azure AD setup guide in `AZURE_AD_SSO_SETUP.md`

## ✨ Ready to Go!
Your React app is fully configured and ready to run. Just:
1. Set up your `.env` file
2. Run `start-dev.bat`
3. Start building!

---
*Last Updated: September 2025*
*DentWizard E-Commerce Platform v1.0*