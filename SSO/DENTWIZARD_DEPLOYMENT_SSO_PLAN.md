# DentWizard Deployment & SSO Integration Plan

## Executive Summary
This plan outlines the complete process to:
1. Set up GitHub repository with proper structure
2. Deploy to Render.com for hosting
3. Maintain local development workflow
4. Integrate SAML 2.0 SSO with Azure AD (Microsoft Entra ID)

**Timeline Estimate:** 2-3 days for full implementation
**Complexity:** Medium (requires coordination between multiple services)

---

## Part 1: GitHub Repository Setup

### 1.1 Repository Structure
```
dentwizard/
├── .github/
│   └── workflows/
│       └── deploy-render.yml          # CI/CD for automatic deployment
├── API/
│   ├── v1/
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   ├── saml-login.php        # NEW: SAML SSO endpoint
│   │   │   ├── saml-callback.php     # NEW: SAML callback handler
│   │   │   └── saml-metadata.php     # NEW: Service Provider metadata
│   │   ├── orders/
│   │   ├── products/
│   │   └── user/
│   ├── config/
│   │   ├── database.php              # Database connection
│   │   ├── saml-config.php           # NEW: SAML configuration
│   │   └── .env.example              # Environment variable template
│   └── vendor/                        # PHP dependencies
├── react-app/
│   ├── src/
│   ├── public/
│   ├── package.json
│   └── .env.example
├── sso/
│   ├── certificates/
│   │   └── LeaderGraphics.cer        # Azure AD certificate
│   ├── metadata/
│   │   └── LeaderGraphics.xml        # Azure AD metadata
│   └── README.md                      # SSO setup instructions
├── docs/
│   ├── API_DOCUMENTATION.md
│   ├── SSO_SETUP.md
│   └── DEPLOYMENT.md
├── .gitignore
├── .env.example
├── README.md
└── render.yaml                        # Render configuration

```

### 1.2 Create .gitignore
```gitignore
# Environment variables
.env
.env.local
.env.production

# Dependencies
node_modules/
vendor/

# Build files
react-app/build/
react-app/.env
react-app/.env.production

# Database credentials
API/config/database.php
API/config/.env

# IDE
.vscode/
.idea/
*.swp
*.swo

# OS files
.DS_Store
Thumbs.db

# Logs
*.log
npm-debug.log*
yarn-debug.log*

# Backup files
*.backup
*.bak
*~

# API test files (optional - you may want these)
API/v1/*/test-*.php
API/v1/*/debug-*.php
```

### 1.3 Initialize Git Repository

**Commands to run:**
```bash
cd C:\Users\jkrug\OneDrive\AI\Claude\dentwizard

# Initialize repository
git init

# Create .gitignore file first
# (copy content from section 1.2 above)

# Add all files
git add .

# Initial commit
git commit -m "Initial commit: DentWizard e-commerce platform with API and React frontend"

# Create main branch (if needed)
git branch -M main
```

### 1.4 Create GitHub Repository

**Steps:**
1. Go to https://github.com/new
2. Repository name: `dentwizard`
3. Description: "DentWizard e-commerce platform with React frontend and PHP API"
4. Choose: **Private** (recommended for business applications)
5. **DO NOT** initialize with README, .gitignore, or license (we already have these)
6. Click "Create repository"

**Connect local repo to GitHub:**
```bash
# Replace YOUR_USERNAME with your GitHub username
git remote add origin https://github.com/YOUR_USERNAME/dentwizard.git

# Push to GitHub
git push -u origin main
```

---

## Part 2: Environment Configuration

### 2.1 Environment Variables Structure

#### For Local Development (.env.local)
```env
# Database
DB_HOST=localhost
DB_NAME=rwaf
DB_USER=rwaf
DB_PASSWORD=Py*uhb$L$##

# App Settings
NODE_ENV=development
API_BASE_URL=http://localhost:3000/API/v1
REACT_APP_API_URL=http://localhost:3000/API/v1

# SAML Settings (will be configured later)
SAML_ENABLED=false
SAML_ENTITY_ID=
SAML_SSO_URL=
SAML_CERT_PATH=
```

#### For Production (.env.production)
```env
# Database - Remote MySQL on Existing PHP Server
DB_HOST=your-server-ip-or-domain.com
DB_PORT=3306
DB_NAME=rwaf
DB_USER=rwaf_remote
DB_PASSWORD=your_secure_password

# App Settings
NODE_ENV=production
API_BASE_URL=https://dentwizard-api.onrender.com/API/v1
REACT_APP_API_URL=https://dentwizard-api.onrender.com/API/v1

# SAML Settings
SAML_ENABLED=true
SAML_ENTITY_ID=https://dentwizard.onrender.com
SAML_SSO_URL=https://login.microsoftonline.com/ea1c5a3f-4d62-491a-8ba4-2e9955015493/saml2
SAML_IDP_ENTITY_ID=https://sts.windows.net/ea1c5a3f-4d62-491a-8ba4-2e9955015493/
SAML_CERT_PATH=/etc/secrets/saml/LeaderGraphics.cer
SAML_CALLBACK_URL=https://dentwizard.onrender.com/API/v1/auth/saml-callback
```

### 2.2 Create Environment Template Files

Create these files to commit to Git (without sensitive data):

**.env.example** (root directory):
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_username
DB_PASSWORD=your_password

# API Configuration
API_BASE_URL=http://localhost:3000/API/v1

# SAML SSO Configuration
SAML_ENABLED=false
SAML_ENTITY_ID=https://your-app.com
SAML_SSO_URL=https://your-sso-provider.com/saml2
SAML_CALLBACK_URL=https://your-app.com/API/v1/auth/saml-callback
```

**react-app/.env.example**:
```env
REACT_APP_API_URL=http://localhost:3000/API/v1
REACT_APP_SSO_ENABLED=false
```

---

## Part 3: Render.com Deployment Setup

### 3.1 Account Setup
1. Go to https://render.com
2. Sign up with your GitHub account
3. This will allow automatic deployments from GitHub

### 3.2 Database Setup - Using Your Existing MySQL Server

**Architecture:** You'll continue using your existing MySQL database (`rwaf`) on your current PHP server, with Render's API connecting to it remotely.

**Step 1: Configure MySQL Server for Remote Access**

**Update MySQL Configuration (my.cnf or my.ini):**
```ini
[mysqld]
# Allow remote connections
bind-address = 0.0.0.0
# Or bind to specific network interface
```

**Restart MySQL after configuration change:**
```bash
# Linux
sudo systemctl restart mysql

# Windows
net stop MySQL80 && net start MySQL80
```

**Step 2: Create Remote Database User**

Connect to MySQL and create a user for Render:

```sql
-- Create remote user with secure password
CREATE USER 'rwaf_remote'@'%' IDENTIFIED BY 'YOUR_SECURE_PASSWORD_HERE';

-- Grant privileges on rwaf database
GRANT ALL PRIVILEGES ON rwaf.* TO 'rwaf_remote'@'%';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify user was created
SELECT user, host FROM mysql.user WHERE user = 'rwaf_remote';
```

**Security Best Practice - IP Whitelisting:**

After you get Render's outbound IP addresses, restrict access to only those IPs:

```sql
-- Drop the wildcard user
DROP USER 'rwaf_remote'@'%';

-- Create user restricted to Render's IP addresses
CREATE USER 'rwaf_remote'@'render_ip_address_1' IDENTIFIED BY 'YOUR_SECURE_PASSWORD_HERE';
CREATE USER 'rwaf_remote'@'render_ip_address_2' IDENTIFIED BY 'YOUR_SECURE_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON rwaf.* TO 'rwaf_remote'@'render_ip_address_1';
GRANT ALL PRIVILEGES ON rwaf.* TO 'rwaf_remote'@'render_ip_address_2';
FLUSH PRIVILEGES;
```

**Step 3: Configure Server Firewall**

**Windows Firewall:**
```powershell
# Open MySQL port (3306) for incoming connections
New-NetFirewallRule -DisplayName "MySQL Remote" -Direction Inbound -Protocol TCP -LocalPort 3306 -Action Allow
```

**Linux (UFW):**
```bash
# Allow MySQL from specific IPs (recommended)
sudo ufw allow from render_ip_address to any port 3306

# Or allow from anywhere (less secure)
sudo ufw allow 3306/tcp
```

**Step 4: Test Remote Connection**

From your local machine, test the remote connection:

```bash
# Test connection
mysql -h your-server-ip.com -u rwaf_remote -p rwaf

# Or use telnet to test port accessibility
telnet your-server-ip.com 3306
```

**Step 5: Get Server Connection Details**

Document these for Render configuration:
- **DB_HOST:** Your server IP or domain (e.g., `123.456.789.0` or `db.yourcompany.com`)
- **DB_PORT:** `3306` (default MySQL port)
- **DB_NAME:** `rwaf`
- **DB_USER:** `rwaf_remote`
- **DB_PASSWORD:** The secure password you created

**Security Recommendations:**

1. **Use SSL/TLS for connections** (recommended for production)
2. **Use strong password** (20+ characters, mixed case, numbers, symbols)
3. **IP whitelist only Render's IPs** (get from Render dashboard)
4. **Monitor access logs** for unauthorized attempts
5. **Keep MySQL updated** with latest security patches

**Getting Render's IP Addresses:**

After deploying your API service on Render:
1. Go to Render Dashboard → Your Web Service → Settings
2. Scroll to "Outbound IP Addresses"
3. Copy all IP addresses listed
4. Use these in your firewall and MySQL user configuration

**Advantages of This Approach:**
- ✅ No database migration required
- ✅ Keep existing backup strategies
- ✅ Lower Render costs (~$7/month savings)
- ✅ Familiar database management
- ✅ Existing database optimizations preserved

**Considerations:**
- ⚠️ Network latency: ~10-50ms additional per query (depends on distance)
- ⚠️ Security: Database exposed to internet (mitigated with IP whitelisting + SSL)
- ⚠️ Reliability: Depends on both Render AND your server uptime
- ⚠️ Connection limits: Monitor concurrent connections

### 3.3 Web Service Setup (PHP API)

**Create Web Service:**
1. Click "New +" → "Web Service"
2. Connect to your GitHub repository
3. Configuration:
   - **Name:** `dentwizard-api`
   - **Environment:** `PHP`
   - **Region:** Same as database
   - **Branch:** `main`
   - **Root Directory:** `/API`
   - **Build Command:** `composer install` (if using Composer)
   - **Start Command:** Leave empty (uses default PHP server)

**Environment Variables (in Render dashboard):**
```
# Remote MySQL Database Connection
DB_HOST=your-server-ip-or-domain.com
DB_PORT=3306
DB_NAME=rwaf
DB_USER=rwaf_remote
DB_PASSWORD=<mark_as_secret_in_dashboard>

# API Settings
API_BASE_URL=https://dentwizard-api.onrender.com/v1

# SAML SSO Settings
SAML_ENABLED=true
SAML_ENTITY_ID=https://dentwizard-api.onrender.com
SAML_SSO_URL=https://login.microsoftonline.com/ea1c5a3f-4d62-491a-8ba4-2e9955015493/saml2
```

**Important:** Mark `DB_PASSWORD` as a secret in Render dashboard to prevent it from appearing in logs.

### 3.4 Static Site Setup (React Frontend)

**Create Static Site:**
1. Click "New +" → "Static Site"
2. Connect to same GitHub repository
3. Configuration:
   - **Name:** `dentwizard-frontend`
   - **Branch:** `main`
   - **Root Directory:** `/react-app`
   - **Build Command:** `npm install && npm run build`
   - **Publish Directory:** `build`

**Environment Variables:**
```
REACT_APP_API_URL=https://dentwizard-api.onrender.com/v1
REACT_APP_SSO_ENABLED=true
```

### 3.5 render.yaml Configuration

Create `render.yaml` in root directory for Infrastructure as Code:

```yaml
services:
  # PHP API Backend
  - type: web
    name: dentwizard-api
    runtime: php
    rootDir: ./API
    buildCommand: composer install
    env: php
    healthCheckPath: /v1/health
    envVars:
      # Remote MySQL Database Connection
      - key: DB_HOST
        sync: false  # Manually set in Render dashboard
      - key: DB_PORT
        value: 3306
      - key: DB_NAME
        value: rwaf
      - key: DB_USER
        sync: false  # Manually set in Render dashboard
      - key: DB_PASSWORD
        sync: false  # Mark as secret in Render dashboard
      
      # API Configuration
      - key: API_BASE_URL
        value: https://dentwizard-api.onrender.com/v1
      
      # SAML SSO Configuration
      - key: SAML_ENABLED
        value: true
      - key: SAML_SSO_URL
        value: https://login.microsoftonline.com/ea1c5a3f-4d62-491a-8ba4-2e9955015493/saml2
      - key: SAML_ENTITY_ID
        fromService:
          type: web
          name: dentwizard-api
          envVarKey: RENDER_EXTERNAL_URL

  # React Frontend
  - type: web
    name: dentwizard-frontend
    runtime: static
    rootDir: ./react-app
    buildCommand: npm install && npm run build
    staticPublishPath: ./build
    envVars:
      - key: REACT_APP_API_URL
        fromService:
          type: web
          name: dentwizard-api
          envVarKey: RENDER_EXTERNAL_URL
      - key: REACT_APP_SSO_ENABLED
        value: true

# NOTE: No database section needed since we're using an external MySQL server
```

**Important Notes:**
- Variables marked with `sync: false` must be manually set in Render dashboard
- `DB_HOST`, `DB_USER`, and `DB_PASSWORD` should be configured as secrets
- Get Render's outbound IP addresses after first deployment to whitelist in MySQL

---

## Part 4: Local Development Workflow

### 4.0 Optional: Enhanced Database Security with SSL/TLS

**For production environments, consider encrypting the database connection:**

**Step 1: Generate SSL Certificates (on your MySQL server)**

```bash
# Create SSL directory
mkdir -p /etc/mysql/ssl
cd /etc/mysql/ssl

# Generate CA key and certificate
openssl genrsa 2048 > ca-key.pem
openssl req -new -x509 -nodes -days 3650 -key ca-key.pem -out ca-cert.pem

# Generate server certificate
openssl req -newkey rsa:2048 -days 3650 -nodes -keyout server-key.pem -out server-req.pem
openssl rsa -in server-key.pem -out server-key.pem
openssl x509 -req -in server-req.pem -days 3650 -CA ca-cert.pem -CAkey ca-key.pem -set_serial 01 -out server-cert.pem

# Set permissions
chmod 600 *.pem
```

**Step 2: Configure MySQL to use SSL**

Add to `my.cnf`:
```ini
[mysqld]
ssl-ca=/etc/mysql/ssl/ca-cert.pem
ssl-cert=/etc/mysql/ssl/server-cert.pem
ssl-key=/etc/mysql/ssl/server-key.pem
```

Restart MySQL.

**Step 3: Require SSL for remote user**

```sql
ALTER USER 'rwaf_remote'@'%' REQUIRE SSL;
FLUSH PRIVILEGES;
```

**Step 4: Update PHP connection code**

In your `API/config/database.php`:
```php
<?php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'rwaf';
$db_user = getenv('DB_USER') ?: 'rwaf';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_port = getenv('DB_PORT') ?: '3306';

// Create connection with SSL
$mysqli = mysqli_init();

// Set SSL options
$mysqli->ssl_set(
    null,                                    // key
    null,                                    // cert
    '/path/to/ca-cert.pem',                 // ca cert (upload to Render)
    null,                                    // capath
    null                                     // cipher
);

// Connect with SSL
$mysqli->real_connect(
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    $db_port,
    null,
    MYSQLI_CLIENT_SSL
);

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Verify SSL connection
$result = $mysqli->query("SHOW STATUS LIKE 'Ssl_cipher'");
$row = $result->fetch_assoc();
if (empty($row['Value'])) {
    error_log('WARNING: MySQL connection is not using SSL');
}
?>
```

**Step 5: Upload CA certificate to Render**

1. Upload `ca-cert.pem` to your repository in a secure location
2. Reference it in your connection code
3. Alternatively, use Render's secret file feature

**Benefits of SSL/TLS:**
- ✅ Encrypts all data in transit
- ✅ Protects against man-in-the-middle attacks
- ✅ Industry best practice for production
- ✅ Required for PCI compliance if handling payments

---

## Part 4: Local Development Workflow

### 4.1 Development Setup

**Initial Setup (one-time):**
```bash
# Clone repository
git clone https://github.com/YOUR_USERNAME/dentwizard.git
cd dentwizard

# Copy environment files
cp .env.example .env.local
cp react-app/.env.example react-app/.env

# Edit .env.local with your local database credentials

# Install React dependencies
cd react-app
npm install

# Start React dev server
npm start
# Runs on http://localhost:3000
```

**PHP API Setup:**
```bash
# If using Composer for dependencies
cd ../API
composer install

# Start PHP built-in server (or use XAMPP/WAMP/MAMP)
php -S localhost:8000

# Or configure in Apache/Nginx to point to /API directory
```

### 4.2 Daily Development Workflow

```bash
# 1. Pull latest changes
git pull origin main

# 2. Create feature branch
git checkout -b feature/your-feature-name

# 3. Make changes to code

# 4. Test locally
cd react-app && npm start

# 5. Commit changes
git add .
git commit -m "Description of changes"

# 6. Push to GitHub
git push origin feature/your-feature-name

# 7. Create Pull Request on GitHub
# 8. After review, merge to main
# 9. Render will automatically deploy
```

### 4.3 Branch Strategy

```
main (production)
  └── develop (staging/testing)
       ├── feature/order-history
       ├── feature/sso-integration
       └── bugfix/checkout-issues
```

**Recommended workflow:**
1. `main` = Production (deployed to Render)
2. `develop` = Staging/Testing
3. `feature/*` = New features
4. `bugfix/*` = Bug fixes
5. `hotfix/*` = Urgent production fixes

---

## Part 5: SAML 2.0 SSO Integration

### 5.1 Understanding Your SSO Setup

**From the metadata file analysis:**
- **Identity Provider:** Microsoft Azure AD (Entra ID)
- **Tenant ID:** ea1c5a3f-4d62-491a-8ba4-2e9955015493
- **SSO Endpoint:** https://login.microsoftonline.com/ea1c5a3f-4d62-491a-8ba4-2e9955015493/saml2
- **Entity ID:** https://sts.windows.net/ea1c5a3f-4d62-491a-8ba4-2e9955015493/
- **Protocol:** SAML 2.0
- **Certificate Expiry:** September 10, 2028

**Available User Claims:**
- Email address
- First name / Last name
- Display name
- Groups (user groups from Azure AD)
- Roles
- Object ID (unique user identifier)

### 5.2 PHP SAML Library Setup

**Install OneLogin SAML PHP Library:**

Create `composer.json` in `/API` directory:
```json
{
    "require": {
        "onelogin/php-saml": "^4.0",
        "php": ">=7.4"
    }
}
```

**Run:**
```bash
cd API
composer install
```

### 5.3 SAML Configuration File

Create `API/config/saml-config.php`:

```php
<?php
// SAML 2.0 Configuration for Azure AD

$samlSettings = array(
    // Service Provider (Your Application) settings
    'sp' => array(
        'entityId' => getenv('SAML_ENTITY_ID') ?: 'https://dentwizard.onrender.com',
        'assertionConsumerService' => array(
            'url' => getenv('SAML_CALLBACK_URL') ?: 'https://dentwizard.onrender.com/API/v1/auth/saml-callback',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        'singleLogoutService' => array(
            'url' => getenv('SAML_SLO_URL') ?: 'https://dentwizard.onrender.com/API/v1/auth/saml-logout',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
        'x509cert' => '', // Optional: SP certificate
        'privateKey' => '', // Optional: SP private key
    ),

    // Identity Provider (Azure AD) settings
    'idp' => array(
        'entityId' => 'https://sts.windows.net/ea1c5a3f-4d62-491a-8ba4-2e9955015493/',
        'singleSignOnService' => array(
            'url' => 'https://login.microsoftonline.com/ea1c5a3f-4d62-491a-8ba4-2e9955015493/saml2',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'singleLogoutService' => array(
            'url' => 'https://login.microsoftonline.com/ea1c5a3f-4d62-491a-8ba4-2e9955015493/saml2',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // Load certificate from file
        'x509cert' => file_get_contents(__DIR__ . '/../sso/certificates/LeaderGraphics.cer'),
    ),

    // Security settings
    'security' => array(
        'nameIdEncrypted' => false,
        'authnRequestsSigned' => false,
        'logoutRequestSigned' => false,
        'logoutResponseSigned' => false,
        'signMetadata' => false,
        'wantMessagesSigned' => false,
        'wantAssertionsSigned' => true,
        'wantAssertionsEncrypted' => false,
        'wantNameIdEncrypted' => false,
        'requestedAuthnContext' => true,
        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
    ),
);

return $samlSettings;
```

### 5.4 SAML Authentication Endpoints

**Create `API/v1/auth/saml-login.php`:**

```php
<?php
// SAML SSO Login Initiator
require_once '../../../vendor/autoload.php';
require_once '../../config/saml-config.php';

use OneLogin\Saml2\Auth;

try {
    $auth = new Auth($samlSettings);
    
    // Redirect to Azure AD login page
    $auth->login();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'SSO initialization failed',
        'message' => $e->getMessage()
    ]);
}
```

**Create `API/v1/auth/saml-callback.php`:**

```php
<?php
// SAML SSO Callback Handler
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../../../vendor/autoload.php';
require_once '../../config/saml-config.php';
require_once '../../config/database.php';

use OneLogin\Saml2\Auth;

try {
    $auth = new Auth($samlSettings);
    $auth->processResponse();
    
    $errors = $auth->getErrors();
    
    if (!empty($errors)) {
        throw new Exception('SAML Authentication Error: ' . implode(', ', $errors));
    }
    
    if (!$auth->isAuthenticated()) {
        throw new Exception('User not authenticated');
    }
    
    // Get user attributes from SAML response
    $attributes = $auth->getAttributes();
    $nameId = $auth->getNameId();
    
    // Extract user information
    $email = $nameId; // Email is the NameID
    $firstName = isset($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'][0]) 
                 ? $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'][0] 
                 : '';
    $lastName = isset($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'][0]) 
                ? $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'][0] 
                : '';
    $displayName = isset($attributes['http://schemas.microsoft.com/identity/claims/displayname'][0]) 
                   ? $attributes['http://schemas.microsoft.com/identity/claims/displayname'][0] 
                   : "$firstName $lastName";
    
    // Check if user exists in database
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $email_safe = $mysqli->real_escape_string($email);
    $query = "SELECT * FROM Users WHERE Email = '$email_safe' LIMIT 1";
    $result = $mysqli->query($query);
    
    if ($result->num_rows === 0) {
        // Create new user (SSO auto-provisioning)
        $name_safe = $mysqli->real_escape_string($displayName);
        $firstName_safe = $mysqli->real_escape_string($firstName);
        $lastName_safe = $mysqli->real_escape_string($lastName);
        
        $insert_query = "INSERT INTO Users (Email, Name, FirstName, LastName, Login, CID, Status) 
                        VALUES ('$email_safe', '$name_safe', '$firstName_safe', '$lastName_safe', '$email_safe', 244, 'Active')";
        
        if (!$mysqli->query($insert_query)) {
            throw new Exception('Failed to create user account');
        }
        
        $user_id = $mysqli->insert_id;
    } else {
        $user = $result->fetch_assoc();
        $user_id = $user['ID'];
    }
    
    // Create session token (same as regular login)
    $token = base64_encode($user_id . ':' . time() . ':' . bin2hex(random_bytes(16)));
    
    // Redirect to frontend with token
    $frontend_url = getenv('FRONTEND_URL') ?: 'https://dentwizard.onrender.com';
    header("Location: $frontend_url/auth/callback?token=$token&user_id=$user_id");
    exit();
    
} catch (Exception $e) {
    error_log('SAML Error: ' . $e->getMessage());
    
    $frontend_url = getenv('FRONTEND_URL') ?: 'https://dentwizard.onrender.com';
    header("Location: $frontend_url/login?error=sso_failed&message=" . urlencode($e->getMessage()));
    exit();
}
```

**Create `API/v1/auth/saml-metadata.php`:**

```php
<?php
// Service Provider Metadata for Azure AD
require_once '../../../vendor/autoload.php';
require_once '../../config/saml-config.php';

use OneLogin\Saml2\Auth;

try {
    $auth = new Auth($samlSettings);
    $settings = $auth->getSettings();
    $metadata = $settings->getSPMetadata();
    $errors = $settings->validateMetadata($metadata);
    
    if (empty($errors)) {
        header('Content-Type: text/xml');
        echo $metadata;
    } else {
        throw new Exception('Invalid SP metadata: ' . implode(', ', $errors));
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
```

### 5.5 React Frontend SSO Integration

**Update `react-app/src/pages/LoginPage.js`:**

```javascript
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

const LoginPage = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const ssoEnabled = process.env.REACT_APP_SSO_ENABLED === 'true';

  const handleSSOLogin = () => {
    // Redirect to SAML login endpoint
    window.location.href = `${process.env.REACT_APP_API_URL}/auth/saml-login`;
  };

  const handleRegularLogin = async (email, password) => {
    // Your existing login logic
    setLoading(true);
    try {
      const response = await api.post('/auth/login', { email, password });
      // Handle response...
    } catch (error) {
      console.error('Login failed:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-container">
      <h1>Login to DentWizard</h1>
      
      {ssoEnabled && (
        <div className="sso-section">
          <button 
            onClick={handleSSOLogin}
            className="sso-button"
          >
            <img src="/microsoft-logo.png" alt="Microsoft" />
            Sign in with Microsoft
          </button>
          <div className="divider">OR</div>
        </div>
      )}

      {/* Regular login form */}
      <form onSubmit={(e) => { 
        e.preventDefault(); 
        handleRegularLogin(email, password);
      }}>
        {/* Your existing form fields */}
      </form>
    </div>
  );
};

export default LoginPage;
```

**Create `react-app/src/pages/AuthCallbackPage.js`:**

```javascript
import React, { useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import { setUser } from '../store/slices/authSlice';
import api from '../services/api';

const AuthCallbackPage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const [searchParams] = useSearchParams();

  useEffect(() => {
    const token = searchParams.get('token');
    const userId = searchParams.get('user_id');
    const error = searchParams.get('error');

    if (error) {
      console.error('SSO Error:', searchParams.get('message'));
      navigate('/login?error=' + error);
      return;
    }

    if (token && userId) {
      // Store authentication
      localStorage.setItem('authToken', token);
      localStorage.setItem('userId', userId);
      
      // Fetch user profile
      api.get('/user/profile')
        .then(response => {
          dispatch(setUser(response.data));
          navigate('/');
        })
        .catch(err => {
          console.error('Failed to fetch profile:', err);
          navigate('/login?error=profile_fetch_failed');
        });
    } else {
      navigate('/login?error=invalid_callback');
    }
  }, [searchParams, navigate, dispatch]);

  return (
    <div className="auth-callback">
      <div className="loader">Completing sign in...</div>
    </div>
  );
};

export default AuthCallbackPage;
```

### 5.6 Azure AD Configuration

**Steps to configure in Azure Portal:**

1. **Go to Azure Portal** (https://portal.azure.com)
2. Navigate to **Azure Active Directory** → **Enterprise Applications**
3. Click **New Application** → **Create your own application**
4. Name: "DentWizard"
5. Select: "Integrate any other application you don't find in the gallery"

**Configure SAML:**
1. Go to **Single sign-on** → Select **SAML**
2. **Basic SAML Configuration:**
   - **Identifier (Entity ID):** `https://dentwizard.onrender.com`
   - **Reply URL (Assertion Consumer Service URL):** `https://dentwizard.onrender.com/API/v1/auth/saml-callback`
   - **Sign on URL:** `https://dentwizard.onrender.com/login`
   - **Logout URL:** `https://dentwizard.onrender.com/API/v1/auth/saml-logout`

3. **Attributes & Claims:**
   - Ensure these claims are included:
     - Email address (required)
     - Given name
     - Surname
     - Display name

4. **User Assignment:**
   - Go to **Users and groups**
   - Add the users who should have access

5. **Test:**
   - Use Azure's "Test" button to verify configuration

### 5.7 Security Considerations

**Important Security Steps:**

1. **HTTPS Only:** Ensure all environments use HTTPS
2. **Certificate Validation:** Verify Azure AD certificate is valid
3. **Session Management:** Implement proper session timeout
4. **CSRF Protection:** Add CSRF tokens to forms
5. **Logging:** Log all SSO attempts for audit

**Add to `API/v1/auth/saml-callback.php`:**
```php
// Log SSO authentication attempt
$log_query = "INSERT INTO auth_logs (user_id, auth_method, ip_address, timestamp, success) 
              VALUES ('$user_id', 'SAML_SSO', '{$_SERVER['REMOTE_ADDR']}', NOW(), 1)";
$mysqli->query($log_query);
```

---

## Part 6: CI/CD Pipeline

### 6.1 GitHub Actions Workflow

Create `.github/workflows/deploy-render.yml`:

```yaml
name: Deploy to Render

on:
  push:
    branches:
      - main
  pull_request:
    branches:
      - main

jobs:
  test-frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      
      - name: Install dependencies
        working-directory: ./react-app
        run: npm ci
      
      - name: Run tests
        working-directory: ./react-app
        run: npm test -- --watchAll=false
      
      - name: Build
        working-directory: ./react-app
        run: npm run build

  test-api:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      
      - name: Install Composer dependencies
        working-directory: ./API
        run: composer install --prefer-dist --no-progress
      
      - name: Run PHP linter
        working-directory: ./API
        run: find . -name "*.php" -exec php -l {} \;

  deploy:
    needs: [test-frontend, test-api]
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - name: Trigger Render deployment
        run: |
          curl -X POST "${{ secrets.RENDER_DEPLOY_HOOK_URL }}"
```

**Add Render Deploy Hook:**
1. Go to Render dashboard → Your service → Settings
2. Scroll to "Deploy Hook"
3. Copy the URL
4. In GitHub: Repository → Settings → Secrets → Actions
5. Add secret: `RENDER_DEPLOY_HOOK_URL` with the copied URL

---

## Part 7: Testing Plan

### 7.1 Local Testing Checklist

**Before pushing to GitHub:**
- [ ] Test regular login works
- [ ] Test cart functionality
- [ ] Test checkout process
- [ ] Test order history
- [ ] Verify API endpoints return correct data
- [ ] Check browser console for errors
- [ ] Test on different browsers (Chrome, Firefox, Safari)

### 7.2 SSO Testing Plan

**After deployment:**
- [ ] Test SSO login initiates correctly
- [ ] Verify redirect to Microsoft login page
- [ ] Test successful authentication flow
- [ ] Verify user account creation on first login
- [ ] Test logout functionality
- [ ] Verify session timeout works
- [ ] Test access with multiple user accounts
- [ ] Verify user role/permissions

### 7.3 Production Testing

**After Render deployment:**
- [ ] Test all API endpoints
- [ ] Verify database connection
- [ ] Check CORS headers
- [ ] Test file uploads (if any)
- [ ] Monitor error logs
- [ ] Verify SSL certificate
- [ ] Test payment processing (if applicable)
- [ ] Load testing (optional)

---

## Part 8: Rollback Plan

### 8.1 Quick Rollback Steps

**If deployment fails:**

1. **Render Dashboard:**
   - Go to your service
   - Click "Deploys"
   - Find previous working deployment
   - Click "Redeploy"

2. **GitHub:**
   ```bash
   # Revert last commit
   git revert HEAD
   git push origin main
   
   # Or rollback to specific commit
   git reset --hard <commit-hash>
   git push -f origin main
   ```

### 8.2 Database Backup Strategy

**Before major changes:**
```bash
# Backup MySQL database
mysqldump -u rwaf -p rwaf > backup_$(date +%Y%m%d).sql

# Upload to secure location
# Store in AWS S3, Google Cloud Storage, or similar
```

**Automated backups on Render:**
- Render automatically backs up databases
- Accessible in dashboard → Database → Backups
- Configure backup frequency in settings

---

## Part 9: Monitoring & Maintenance

### 9.1 Monitoring Setup

**Render provides:**
- Real-time logs
- Performance metrics
- Error tracking
- Uptime monitoring

**Additional tools to consider:**
- **Sentry** for error tracking (sentry.io)
- **LogRocket** for session replay
- **Google Analytics** for user tracking
- **StatusCake** for uptime monitoring

### 9.2 Regular Maintenance Tasks

**Weekly:**
- [ ] Review error logs
- [ ] Check database size/growth
- [ ] Monitor API response times
- [ ] Review user feedback

**Monthly:**
- [ ] Update dependencies (npm, Composer)
- [ ] Security audit
- [ ] Performance review
- [ ] Database optimization
- [ ] Backup verification

**Quarterly:**
- [ ] SSL certificate check (auto-renews on Render)
- [ ] Azure AD certificate expiry check (expires 2028)
- [ ] User access audit
- [ ] Disaster recovery drill

---

## Part 10: Documentation

### 10.1 Required Documentation Files

Create these in `/docs` directory:

1. **API_DOCUMENTATION.md**
   - All API endpoints
   - Request/response examples
   - Authentication methods
   - Error codes

2. **SSO_SETUP.md**
   - Azure AD configuration steps
   - Troubleshooting guide
   - User provisioning process

3. **DEPLOYMENT.md**
   - Deployment process
   - Environment variables
   - Rollback procedures

4. **DEVELOPER_GUIDE.md**
   - Local setup instructions
   - Coding standards
   - Git workflow
   - Testing procedures

---

## Implementation Checklist

### Phase 1: Repository Setup (Day 1 - Morning)
- [ ] Create .gitignore file
- [ ] Initialize Git repository
- [ ] Create GitHub repository
- [ ] Push initial commit
- [ ] Create .env.example files
- [ ] Document environment variables

### Phase 2: Render Setup (Day 1 - Afternoon)
- [ ] Create Render account
- [ ] Configure remote MySQL server for external connections
- [ ] Create remote database user with secure password
- [ ] Configure server firewall to allow MySQL connections
- [ ] Test remote database connection
- [ ] Configure PHP API service on Render
- [ ] Configure React static site on Render
- [ ] Set environment variables (including remote DB credentials)
- [ ] Get Render's outbound IP addresses
- [ ] Whitelist Render IPs in MySQL and firewall
- [ ] Test initial deployment with database connection

### Phase 3: Local Development (Day 2 - Morning)
- [ ] Clone repository locally
- [ ] Configure local environment
- [ ] Test local development workflow
- [ ] Document setup process
- [ ] Create branch strategy

### Phase 4: SSO Preparation (Day 2 - Afternoon)
- [ ] Install PHP SAML library
- [ ] Create SAML configuration
- [ ] Implement SAML endpoints
- [ ] Update React login page
- [ ] Create auth callback handler

### Phase 5: Azure AD Configuration (Day 3 - Morning)
- [ ] Configure Enterprise Application
- [ ] Set up SAML settings
- [ ] Configure claims
- [ ] Assign test users
- [ ] Upload LeaderGraphics.cer

### Phase 6: Testing & Launch (Day 3 - Afternoon)
- [ ] Test regular login
- [ ] Test SSO login flow
- [ ] Test user provisioning
- [ ] Test logout
- [ ] Monitor logs
- [ ] Deploy to production
- [ ] Final verification

---

## Cost Estimate

### Render.com Pricing (Using External Database):
- **Free Tier:** Good for testing, limited resources

- **Starter ($7/month each):**
  - Web Service (API): $7/month
  - Static Site (React): $7/month
  - Database: $0/month (using your existing MySQL server)
  - **Total: ~$14/month**

- **Professional ($25-50/month each):** Recommended for production
  - Web Service (API): $25/month
  - Static Site (React): $7/month
  - Database: $0/month (using your existing MySQL server)
  - **Total: ~$32/month**

### Your Current Setup:
- Existing PHP/MySQL server: Already paid for
- No additional database hosting costs
- **Savings: $7/month** compared to using Render's database

### Additional Costs:
- Domain name: ~$12-15/year (optional)
- SSL certificate: Free with Render
- Monitoring tools: Free tiers available
- **Estimated Total: $14-35/month** depending on tier selection

### Cost Comparison:

**With Render Database:**
- Starter tier: $21/month
- Professional tier: ~$57/month

**With Your Existing Database (Current Plan):**
- Starter tier: $14/month ✅ **Save $7/month**
- Professional tier: $32/month ✅ **Save $25/month**

### Performance Considerations:

**Network Latency:**
- Local Render DB: ~1-5ms query time
- Remote MySQL: ~10-50ms query time (depends on distance)
- Impact: Minimal for most operations
- Optimization: Connection pooling, query caching

**Cost vs Performance:**
The slight latency increase is usually acceptable for the cost savings, especially for e-commerce applications where database queries aren't the primary bottleneck.

---

## Next Steps

1. **Read through this entire document**
2. **Gather required credentials:**
   - GitHub account
   - Database credentials
   - Azure AD admin access
3. **Set aside 2-3 days for implementation**
4. **Follow checklist in order**
5. **Test thoroughly before go-live**
6. **Keep this document for reference**

---

## Support & Troubleshooting

### Common Issues:

**"Database connection failed"**
- Check environment variables in Render dashboard
- Verify database credentials are correct
- Test connection from your local machine to rule out server issues
- Check firewall rules on your MySQL server
- Verify MySQL is configured to accept remote connections (bind-address = 0.0.0.0)
- Ensure remote user exists: `SELECT user, host FROM mysql.user;`
- Check if Render's IP is whitelisted in your firewall
- Review MySQL error logs on your server

**"Connection timed out" when accessing database**
- Firewall is blocking port 3306
- MySQL server is not listening on external interface
- ISP is blocking outbound connections to port 3306
- Try changing MySQL port to 3307 or another port if 3306 is blocked

**"Access denied for user 'rwaf_remote'@'<render_ip>'"**
- User doesn't have permission from Render's IP address
- Run: `GRANT ALL PRIVILEGES ON rwaf.* TO 'rwaf_remote'@'render_ip'; FLUSH PRIVILEGES;`
- Check user host matches Render's outbound IP
- Verify password is correct

**"Too many connections" error**
- MySQL max_connections limit reached
- Check: `SHOW VARIABLES LIKE 'max_connections';`
- Increase in my.cnf: `max_connections = 200`
- Close unused connections in your PHP code
- Implement connection pooling

**"SSL connection error"**
- CA certificate path is incorrect
- Certificate has expired or is invalid
- MySQL server not configured for SSL
- Verify: `SHOW VARIABLES LIKE '%ssl%';` on MySQL server

**"SAML authentication error"**
- Verify certificate is valid
- Check Azure AD configuration
- Review callback URL matches exactly
- Check Render logs for detailed error messages

**"Build failed on Render"**
- Check build logs in Render dashboard
- Verify package.json is correct
- Check for missing dependencies
- Verify composer.json is present and valid

**"Slow API responses"**
- Database latency due to geographic distance
- Enable query caching in MySQL
- Optimize slow queries (use EXPLAIN)
- Consider connection pooling
- Add database indexes for frequently queried columns
- Monitor with: `SHOW PROCESSLIST;` to see slow queries

**"API works locally but fails on Render"**
- Environment variables not set correctly in Render
- Database credentials different between local and production
- Firewall blocking Render's IP addresses
- PHP version differences
- Missing PHP extensions

### Getting Help:
- Render Documentation: https://render.com/docs
- Render Community Forum: https://community.render.com
- MySQL Documentation: https://dev.mysql.com/doc/
- OneLogin SAML PHP: https://github.com/onelogin/php-saml
- Azure AD SAML: https://learn.microsoft.com/en-us/azure/active-directory/develop/

### Remote Database Checklist:
If experiencing database connection issues, verify:
- [ ] MySQL is running on your server
- [ ] Port 3306 is open in firewall
- [ ] bind-address = 0.0.0.0 in MySQL config
- [ ] Remote user created with correct permissions
- [ ] Render's IP addresses are whitelisted
- [ ] Connection works from local machine
- [ ] Credentials are correct in Render dashboard
- [ ] MySQL error log checked for issues

---

## Conclusion

This plan provides a complete roadmap for:
✅ GitHub repository setup with proper structure
✅ Render.com deployment for production hosting
✅ Local development workflow that mirrors production
✅ SAML 2.0 SSO integration with Azure AD
✅ CI/CD pipeline for automatic deployments
✅ Comprehensive testing and monitoring
✅ Rollback and disaster recovery procedures

**Estimated Timeline:** 2-3 days for full implementation
**Difficulty:** Medium (requires attention to detail)
**Risk:** Low (with proper testing and rollback plan)

Follow this plan step-by-step, and you'll have a professional, scalable deployment with enterprise SSO authentication.
