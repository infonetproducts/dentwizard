# DentWizard E-Commerce Platform

A full-stack e-commerce application with React frontend, PHP API backend, and SAML 2.0 SSO integration.

## 🏗️ Architecture

- **Frontend**: React 18 with Material-UI
- **Backend**: PHP 8.2 API with RESTful endpoints
- **Database**: MySQL (hosted on existing PHP server)
- **Authentication**: SAML 2.0 SSO with Azure AD
- **Hosting**: Render.com
- **Version Control**: GitHub

## 📁 Project Structure

```
dentwizard/
├── API/                    # PHP Backend API
│   ├── v1/                # API version 1
│   │   ├── auth/         # Authentication endpoints
│   │   ├── products/     # Product management
│   │   ├── orders/       # Order management
│   │   └── users/        # User management
│   ├── config/           # Configuration files
│   └── Dockerfile        # Docker configuration for Render
├── react-app/            # React Frontend
│   ├── src/
│   │   ├── components/   # Reusable components
│   │   ├── pages/        # Page components
│   │   ├── services/     # API services
│   │   └── App.js
│   └── package.json
├── SSO/                  # SAML 2.0 configuration
│   ├── LeaderGraphics.xml
│   └── LeaderGraphics.cer
├── .env.example          # Environment variables template
└── render.yaml           # Render deployment config

```

## 🚀 Quick Start

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/infonetproducts/dentwizard.git
   cd dentwizard
   ```

2. **Set up environment variables**
   ```bash
   cp .env.local.example .env
   # Edit .env with your local database credentials
   ```

3. **Install React dependencies**
   ```bash
   cd react-app
   npm install
   ```

4. **Start development servers**
   
   **Terminal 1 - PHP API:**
   ```bash
   cd API
   php -S localhost:8000
   ```
   
   **Terminal 2 - React App:**
   ```bash
   cd react-app
   npm start
   ```

5. **Access the application**
   - Frontend: http://localhost:3000
   - API: http://localhost:8000

## 🔧 Environment Configuration

Create a `.env` file in the project root based on `.env.example`:

```bash
# Database (Remote MySQL)
DB_HOST=your-mysql-server.com
DB_PORT=3306
DB_NAME=rwaf
DB_USER=rwaf_remote
DB_PASSWORD=your_password

# Environment
ENVIRONMENT=development

# API URLs
API_URL=http://localhost:8000
CORS_ORIGIN=http://localhost:3000
```

### React App Environment

Create `react-app/.env`:

```bash
REACT_APP_API_URL=http://localhost:8000
REACT_APP_SAML_ENABLED=false
```

## 🌐 Deployment (Render.com)

### Prerequisites
- GitHub account with repository access
- Render.com account
- MySQL server with remote access configured
- Azure AD tenant for SSO

### Deploy Steps

1. **Push to GitHub** (already done)

2. **Connect to Render**
   - Go to https://dashboard.render.com
   - Click "New +" → "Blueprint"
   - Connect your GitHub repository
   - Render will auto-detect `render.yaml`

3. **Configure Environment Variables**
   In Render dashboard, set these sensitive variables:
   - `DB_HOST` - Your MySQL server address
   - `DB_USER` - Database username
   - `DB_PASSWORD` - Database password

4. **Deploy**
   - Render will automatically build and deploy
   - API: `https://dentwizard-api.onrender.com`
   - Frontend: `https://dentwizard-app.onrender.com`

## 🔐 SAML SSO Configuration

### Azure AD Setup

1. **Register Application**
   - Go to Azure Portal → Azure Active Directory
   - App Registrations → New Registration
   - Name: DentWizard
   - Reply URLs: `https://dentwizard-api.onrender.com/api/v1/auth/saml/callback`

2. **Configure SAML**
   - Enterprise Applications → DentWizard
   - Single Sign-On → SAML
   - Upload `SSO/LeaderGraphics.xml` metadata
   - Set Entity ID: `https://dentwizard-app.onrender.com`

3. **Assign Users**
   - Users and Groups → Add User/Group
   - Assign appropriate users/groups

## 🗄️ Database Setup

Your MySQL database should have remote access enabled:

```sql
-- Create remote user
CREATE USER 'rwaf_remote'@'%' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON rwaf.* TO 'rwaf_remote'@'%';
FLUSH PRIVILEGES;
```

**Firewall Configuration:**
- Open port 3306
- Whitelist Render's IP addresses (get from Render dashboard)

## 📦 Key Features

- ✅ Product catalog with images
- ✅ Shopping cart functionality
- ✅ Order management system
- ✅ Budget tracking
- ✅ User authentication via SAML 2.0
- ✅ Order history and details
- ✅ Responsive design
- ✅ Tax and shipping calculations

## 🔒 Security

- SAML 2.0 SSO with Azure AD
- Environment-based configuration
- Secure database connections
- CORS protection
- SQL injection prevention with prepared statements
- XSS protection in React

## 🛠️ Technology Stack

**Frontend:**
- React 18
- Material-UI (MUI)
- React Router v6
- Axios for API calls

**Backend:**
- PHP 8.2
- MySQLi
- RESTful API design
- OneLogin SAML library

**DevOps:**
- GitHub Actions (CI/CD)
- Render.com hosting
- Docker containerization

## 📝 API Documentation

### Base URL
- Production: `https://dentwizard-api.onrender.com`
- Local: `http://localhost:8000`

### Key Endpoints

**Products**
- `GET /api/v1/products/` - List all products
- `GET /api/v1/products/detail.php?id={id}` - Product details

**Orders**
- `POST /api/v1/orders/create.php` - Create order
- `GET /api/v1/orders/` - List orders
- `GET /api/v1/orders/detail.php?id={id}` - Order details

**Authentication**
- `POST /api/v1/auth/saml/login` - Initiate SAML login
- `POST /api/v1/auth/saml/callback` - SAML callback handler
- `POST /api/v1/auth/saml/logout` - SAML logout

**Users**
- `GET /api/v1/users/` - List users
- `POST /api/v1/users/create.php` - Create user

## 🧪 Testing

```bash
# Run React tests
cd react-app
npm test

# Run E2E tests (if configured)
npm run test:e2e
```

## 🐛 Troubleshooting

**Database Connection Issues:**
```bash
# Test remote MySQL connection
mysql -h your-server.com -u rwaf_remote -p rwaf
```

**CORS Errors:**
- Check `CORS_ORIGIN` in environment variables
- Verify API URL in React app matches actual API URL

**Build Failures:**
- Clear node_modules: `rm -rf node_modules && npm install`
- Clear React cache: `rm -rf react-app/build`

## 📚 Documentation

- [Deployment Plan](SSO/DENTWIZARD_DEPLOYMENT_SSO_PLAN.md) - Comprehensive deployment guide
- [API Documentation](API/README.md) - Detailed API specs (if exists)

## 🤝 Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Make changes and commit: `git commit -am 'Add feature'`
3. Push to branch: `git push origin feature/your-feature`
4. Create Pull Request

## 📄 License

Proprietary - All rights reserved

## 📧 Support

For issues or questions, contact: [support contact info]

---

**Last Updated:** October 2025
