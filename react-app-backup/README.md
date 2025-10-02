# DentWizard E-Commerce React Application

A modern, mobile-first e-commerce application for DentWizard's corporate apparel and merchandise, featuring Azure AD Single Sign-On (SSO) authentication.

## 🚀 Features

- **Azure AD SSO Authentication**: Secure login with corporate credentials
- **Mobile-First Design**: Fully responsive and optimized for all devices
- **Modern UI/UX**: Material-UI components with custom theming
- **Product Catalog**: Browse and filter corporate apparel
- **Shopping Cart**: Real-time cart management with Redux
- **Secure Checkout**: Multi-step checkout process
- **User Profiles**: Manage personal information and addresses
- **Order History**: Track orders and shipments
- **Search & Filter**: Advanced product search capabilities
- **PWA Ready**: Progressive Web App capabilities

## 📋 Prerequisites

- Node.js 14+ and npm/yarn
- Azure AD tenant with configured application
- Backend API running (see API documentation)

## 🔧 Installation

1. **Clone the repository** (if not already done):
```bash
cd C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\react-app
```

2. **Install dependencies**:
```bash
npm install
```

3. **Configure environment variables**:
Create a `.env` file based on `.env.example`:

```env
# Azure AD Configuration
REACT_APP_CLIENT_ID=your_azure_ad_client_id
REACT_APP_TENANT_ID=your_azure_ad_tenant_id
REACT_APP_REDIRECT_URI=http://localhost:3000

# API Configuration
REACT_APP_API_URL=http://localhost:8000/api

# Features
REACT_APP_ENABLE_SSO=true
REACT_APP_ENABLE_GUEST_CHECKOUT=false
```

## 🏃‍♂️ Running the Application

### Development Mode
```bash
npm start
```
The app will open at [http://localhost:3000](http://localhost:3000)

### Production Build
```bash
npm run build
```
Creates optimized production build in the `build` folder.

### Run Tests
```bash
npm test
```

### Analyze Bundle Size
```bash
npm run analyze
```

## 🏗️ Project Structure

```
react-app/
├── public/              # Static assets
│   └── index.html       # HTML template
├── src/
│   ├── components/      # Reusable components
│   │   ├── auth/        # Authentication components
│   │   │   └── ProtectedRoute.js
│   │   └── layout/      # Layout components
│   │       ├── Layout.js
│   │       ├── CartDrawer.js
│   │       └── MobileSearch.js
│   ├── pages/           # Page components
│   │   ├── LoginPage.js
│   │   ├── HomePage.js
│   │   ├── ProductsPage.js
│   │   ├── ProductDetailPage.js
│   │   ├── CartPage.js
│   │   ├── CheckoutPage.js
│   │   ├── ProfilePage.js
│   │   └── OrderHistoryPage.js
│   ├── services/        # API services
│   │   └── api.js       # Axios configuration
│   ├── store/           # Redux store
│   │   ├── store.js
│   │   └── slices/
│   │       ├── userSlice.js
│   │       ├── cartSlice.js
│   │       └── uiSlice.js
│   ├── theme/           # MUI theme
│   │   └── theme.js
│   ├── hooks/           # Custom hooks
│   │   └── useDebounce.js
│   ├── authConfig.js    # Azure AD configuration
│   ├── App.js           # Main app component
│   ├── index.js         # App entry point
│   └── index.css        # Global styles
├── .env.example         # Environment variables template
├── package.json         # Dependencies and scripts
└── README.md           # This file
```

## 🔐 Azure AD Setup

1. **Register Application in Azure Portal**:
   - Go to Azure Portal > Azure Active Directory
   - App registrations > New registration
   - Set redirect URI: `http://localhost:3000` (dev) and production URL

2. **Configure Permissions**:
   - API permissions > Add permission
   - Microsoft Graph > Delegated permissions
   - Select: User.Read, email, profile, openid

3. **Update Authentication**:
   - Authentication > Add platform > Single-page application
   - Enable ID tokens and Access tokens

## 🎨 Customization

### Theme Customization
Edit `src/theme/theme.js`:
```javascript
const theme = createTheme({
  palette: {
    primary: {
      main: '#1976d2', // DentWizard blue
    },
    // ... other colors
  }
});
```

### Adding New Pages
1. Create component in `src/pages/`
2. Add route in `src/App.js`
3. Update navigation in `src/components/layout/Layout.js`

### State Management
Redux slices are in `src/store/slices/`:
- `userSlice.js`: User authentication and profile
- `cartSlice.js`: Shopping cart management
- `uiSlice.js`: UI state (drawers, modals, etc.)

## 🚢 Deployment

### Build for Production
```bash
npm run build
```

### Deploy to Azure Static Web Apps
```bash
# Install Azure Static Web Apps CLI
npm install -g @azure/static-web-apps-cli

# Deploy
swa deploy ./build --deployment-token <token>
```

### Environment Variables for Production
Set these in your hosting platform:
- `REACT_APP_CLIENT_ID`
- `REACT_APP_TENANT_ID`
- `REACT_APP_REDIRECT_URI`
- `REACT_APP_API_URL`

## 📱 Mobile Optimization Features

- **Responsive Grid System**: Adapts to all screen sizes
- **Touch-Optimized**: Swipe gestures for cart and navigation
- **Mobile Navigation**: Bottom navigation on mobile devices
- **Progressive Loading**: Lazy loading for images
- **Offline Support**: Service worker for offline functionality
- **Performance**: Code splitting and optimization

## 🧪 Testing

### Unit Tests
```bash
npm test
```

### E2E Tests (if configured)
```bash
npm run test:e2e
```

### Lighthouse Audit
```bash
npm run build
npx lighthouse http://localhost:3000
```

## 🐛 Troubleshooting

### Azure AD Login Issues
- Verify redirect URI matches exactly
- Check tenant ID and client ID
- Ensure user has proper permissions

### API Connection Issues
- Verify API is running
- Check CORS settings on API
- Confirm API URL in .env file

### Build Issues
```bash
# Clear cache and reinstall
rm -rf node_modules package-lock.json
npm install
```

## 📚 API Integration

The app expects these API endpoints:
- `POST /auth/login` - Azure AD token validation
- `GET /products` - Product listing
- `GET /products/:id` - Product details
- `POST /orders` - Create order
- `GET /orders/my-orders` - User's orders
- `GET /user/profile` - User profile
- `PUT /user/profile` - Update profile

## 🤝 Contributing

1. Create feature branch: `git checkout -b feature/new-feature`
2. Commit changes: `git commit -am 'Add new feature'`
3. Push to branch: `git push origin feature/new-feature`
4. Submit pull request

## 📄 License

Proprietary - DentWizard © 2024

## 💬 Support

For issues or questions:
- Internal: Contact IT Support
- Technical: Check API documentation
- UI/UX: Refer to design system

## 🔄 Version History

- **v1.0.0** - Initial release with core e-commerce features
- **v1.1.0** - Added Azure AD SSO integration
- **v1.2.0** - Mobile optimization and PWA support

## ⚡ Performance Tips

1. **Enable Production Mode**: Use `npm run build` for production
2. **CDN Assets**: Host static assets on CDN
3. **Enable Caching**: Configure service worker
4. **Optimize Images**: Use WebP format where possible
5. **Code Splitting**: Already implemented with React.lazy()

## 🎯 Roadmap

- [ ] Multi-language support
- [ ] Advanced analytics dashboard
- [ ] Wishlist functionality
- [ ] Product reviews and ratings
- [ ] Bulk ordering for departments
- [ ] Integration with inventory system
- [ ] Email notifications
- [ ] PDF invoice generation