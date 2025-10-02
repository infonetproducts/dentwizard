# SSO Integration Complete Implementation Guide

## Overview
This guide provides step-by-step SSO integration for PHP 5.6 with Auth0, Okta, and Azure AD.

## Prerequisites
- PHP 5.6+
- Composer installed
- SSL certificate (SSO requires HTTPS)
- SSO provider account (Auth0/Okta/Azure)

---

## Option 1: Auth0 Integration (Recommended)

### Step 1: Install Auth0 SDK (PHP 5.6 Compatible)
```bash
composer require auth0/auth0-php:^5.0
```

### Step 2: Configure Auth0 Application
1. Log into Auth0 Dashboard
2. Create new Application → Regular Web Application
3. Note these values:
   - Domain: `your-tenant.auth0.com`
   - Client ID: `xxxxxxxxxxxxx`
   - Client Secret: `xxxxxxxxxxxxx`
4. Add Allowed Callback URLs:
   ```
   https://your-react-app.com/callback
   http://localhost:3000/callback
   ```

### Step 3: Complete validate.php Implementation
```php
<?php
// v1/auth/validate.php - Complete Auth0 Implementation
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';
require_once '../../vendor/autoload.php';

use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\CoreException;

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$id_token = isset($input['id_token']) ? $input['id_token'] : null;
$access_token = isset($input['access_token']) ? $input['access_token'] : null;

if (!$id_token && !$access_token) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'No token provided'
    ));
    exit;
}

try {
    // Initialize Auth0
    $auth0 = new Auth0(array(
        'domain' => getenv('AUTH0_DOMAIN'),
        'client_id' => getenv('AUTH0_CLIENT_ID'),
        'client_secret' => getenv('AUTH0_CLIENT_SECRET'),
        'redirect_uri' => getenv('AUTH0_CALLBACK_URL'),
        'audience' => getenv('AUTH0_AUDIENCE'),
        'scope' => 'openid profile email',
        'persist_id_token' => true,
        'persist_access_token' => true,
        'persist_refresh_token' => true
    ));
    
    // For ID token validation
    if ($id_token) {
        // Decode and verify ID token
        $token_verifier = new \Auth0\SDK\Helpers\Tokens\IdTokenVerifier(
            getenv('AUTH0_DOMAIN'),
            getenv('AUTH0_CLIENT_ID')
        );
        
        $decoded = $token_verifier->verify($id_token);
        $user_info = (array)$decoded;
    }
    // For Access token validation
    else if ($access_token) {
        // Use Management API to get user info
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://" . getenv('AUTH0_DOMAIN') . "/userinfo",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $access_token
            )
        ));
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code !== 200) {
            throw new Exception('Invalid access token');
        }
        
        $user_info = json_decode($response, true);
    }
    
    // Extract user details
    $email = isset($user_info['email']) ? $user_info['email'] : null;
    $name = isset($user_info['name']) ? $user_info['name'] : null;
    $auth0_id = isset($user_info['sub']) ? $user_info['sub'] : null;
    $picture = isset($user_info['picture']) ? $user_info['picture'] : null;
    
    if (!$email) {
        throw new Exception('No email in token');
    }
    
    // Database operations
    $pdo = getPDOConnection();
    
    // Check if user exists
    $stmt = $pdo->prepare("
        SELECT UID, Email, Name, CID, UserType 
        FROM Users 
        WHERE Email = :email
    ");
    $stmt->execute(array('email' => $email));
    $user = $stmt->fetch();
    
    if (!$user) {
        // Create new user
        $stmt = $pdo->prepare("
            INSERT INTO Users (Email, Name, Auth0ID, ProfilePicture, CreatedDate, CID) 
            VALUES (:email, :name, :auth0_id, :picture, NOW(), :client_id)
        ");
        $stmt->execute(array(
            'email' => $email,
            'name' => $name,
            'auth0_id' => $auth0_id,
            'picture' => $picture,
            'client_id' => 1  // Default client ID, adjust as needed
        ));
        $user_id = $pdo->lastInsertId();
        $client_id = 1;
        $user_type = 'customer';
    } else {
        // Update last login
        $stmt = $pdo->prepare("
            UPDATE Users 
            SET LastLogin = NOW(), Auth0ID = :auth0_id 
            WHERE UID = :user_id
        ");
        $stmt->execute(array(
            'auth0_id' => $auth0_id,
            'user_id' => $user['UID']
        ));
        
        $user_id = $user['UID'];
        $client_id = $user['CID'];
        $user_type = $user['UserType'];
    }
    
    // Check if user has existing cart in session
    session_start();
    $cart_data = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
    
    // Merge session cart with user cart if exists
    if (!empty($cart_data)) {
        $stmt = $pdo->prepare("
            SELECT cart_data FROM UserCarts WHERE UID = :user_id
        ");
        $stmt->execute(array('user_id' => $user_id));
        $existing_cart = $stmt->fetch();
        
        if ($existing_cart && $existing_cart['cart_data']) {
            $db_cart = json_decode($existing_cart['cart_data'], true);
            $cart_data = array_merge($db_cart, $cart_data);
        }
        
        // Save merged cart
        $cart_json = json_encode($cart_data);
        $stmt = $pdo->prepare("
            INSERT INTO UserCarts (UID, CID, cart_data, created_date, updated_date) 
            VALUES (:user_id, :client_id, :cart_data, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
            cart_data = :cart_data2, updated_date = NOW()
        ");
        $stmt->execute(array(
            'user_id' => $user_id,
            'client_id' => $client_id,
            'cart_data' => $cart_json,
            'cart_data2' => $cart_json
        ));
    }
    
    // Create JWT for API access
    $jwt_token = AuthMiddleware::createToken(array(
        'user_id' => $user_id,
        'email' => $email,
        'name' => $name,
        'client_id' => $client_id,
        'user_type' => $user_type
    ));
    
    // Set session variables for backward compatibility
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
    $_SESSION['client_id'] = $client_id;
    
    // Return success response
    echo json_encode(array(
        'success' => true,
        'token' => $jwt_token,
        'user' => array(
            'id' => $user_id,
            'email' => $email,
            'name' => $name,
            'picture' => $picture,
            'client_id' => $client_id,
            'user_type' => $user_type
        ),
        'cart' => $cart_data
    ));
    
} catch (Exception $e) {
    error_log('Auth0 validation error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode(array(
        'success' => false,
        'error' => 'Authentication failed',
        'details' => getenv('ENV') === 'development' ? $e->getMessage() : null
    ));
}
?>
```

### Step 4: Add to .env file
```ini
# Auth0 Configuration
AUTH0_DOMAIN=your-tenant.auth0.com
AUTH0_CLIENT_ID=xxxxxxxxxxxxx
AUTH0_CLIENT_SECRET=xxxxxxxxxxxxx
AUTH0_CALLBACK_URL=https://your-react-app.com/callback
AUTH0_AUDIENCE=https://your-api-identifier
```

---

## Option 2: Okta Integration

### Step 1: Install Okta SDK
```bash
composer require okta/jwt-verifier:^1.0
```

### Step 2: Configure Okta Application
1. Log into Okta Admin Console
2. Applications → Create App Integration
3. Choose: OIDC - OpenID Connect
4. Application type: Single-Page Application
5. Note these values:
   - Issuer: `https://your-domain.okta.com/oauth2/default`
   - Client ID: `xxxxxxxxxxxxx`

### Step 3: Okta validate.php Implementation
```php
<?php
// v1/auth/validate.php - Okta Implementation
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';
require_once '../../vendor/autoload.php';

use Okta\JwtVerifier\JwtVerifierBuilder;

$input = json_decode(file_get_contents('php://input'), true);
$access_token = isset($input['access_token']) ? $input['access_token'] : null;

if (!$access_token) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'No token provided'
    ));
    exit;
}

try {
    // Build JWT Verifier
    $jwtVerifier = (new JwtVerifierBuilder())
        ->setIssuer(getenv('OKTA_ISSUER'))
        ->setAudience('api://default')
        ->setClientId(getenv('OKTA_CLIENT_ID'))
        ->build();
    
    // Verify token
    $jwt = $jwtVerifier->verify($access_token);
    $claims = $jwt->getClaims();
    
    $email = $claims['email'];
    $name = isset($claims['name']) ? $claims['name'] : $email;
    $okta_id = $claims['sub'];
    
    // Rest of the code is similar to Auth0 implementation
    // (Database operations, JWT creation, etc.)
    
} catch (Exception $e) {
    error_log('Okta validation error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode(array(
        'success' => false,
        'error' => 'Authentication failed'
    ));
}
?>
```

---

## Option 3: Azure AD Integration

### Step 1: Install Azure Dependencies
```bash
composer require firebase/php-jwt:^5.5
```

### Step 2: Configure Azure AD
1. Azure Portal → Azure Active Directory
2. App registrations → New registration
3. Configure:
   - Redirect URI: `https://your-react-app.com/callback`
   - Supported account types: Your choice
4. Note values:
   - Tenant ID: `xxxxx-xxxx-xxxx`
   - Client ID: `xxxxx-xxxx-xxxx`

### Step 3: Azure AD validate.php Implementation
```php
<?php
// v1/auth/validate.php - Azure AD Implementation
require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';
require_once '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

$input = json_decode(file_get_contents('php://input'), true);
$id_token = isset($input['id_token']) ? $input['id_token'] : null;

if (!$id_token) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'No token provided'
    ));
    exit;
}

try {
    // Get Azure AD public keys
    $tenant_id = getenv('AZURE_TENANT_ID');
    $client_id = getenv('AZURE_CLIENT_ID');
    
    $keys_url = "https://login.microsoftonline.com/{$tenant_id}/discovery/v2.0/keys";
    $keys_json = file_get_contents($keys_url);
    $keys = json_decode($keys_json, true);
    
    // Decode token header to get kid
    $token_parts = explode('.', $id_token);
    $header = json_decode(base64_decode($token_parts[0]), true);
    $kid = $header['kid'];
    
    // Find matching key
    $public_key = null;
    foreach ($keys['keys'] as $key) {
        if ($key['kid'] === $kid) {
            $public_key = $key;
            break;
        }
    }
    
    if (!$public_key) {
        throw new Exception('Public key not found');
    }
    
    // Verify token
    $decoded = JWT::decode($id_token, JWK::parseKey($public_key), array('RS256'));
    
    // Validate claims
    if ($decoded->aud !== $client_id) {
        throw new Exception('Invalid audience');
    }
    
    if ($decoded->iss !== "https://login.microsoftonline.com/{$tenant_id}/v2.0") {
        throw new Exception('Invalid issuer');
    }
    
    if ($decoded->exp < time()) {
        throw new Exception('Token expired');
    }
    
    $email = $decoded->email;
    $name = $decoded->name;
    $azure_id = $decoded->sub;
    
    // Rest similar to Auth0 implementation
    // (Database operations, JWT creation, etc.)
    
} catch (Exception $e) {
    error_log('Azure AD validation error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode(array(
        'success' => false,
        'error' => 'Authentication failed'
    ));
}
?>
```

---

## Testing SSO Integration

### 1. Test Auth0 with cURL
```bash
# Get test token from Auth0
curl -X POST https://YOUR_DOMAIN.auth0.com/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET",
    "audience": "YOUR_API_IDENTIFIER",
    "grant_type": "client_credentials"
  }'

# Test validation endpoint
curl -X POST http://your-server.com/API/v1/auth/validate.php \
  -H "Content-Type: application/json" \
  -d '{"access_token": "YOUR_TOKEN"}'
```

### 2. Test with React (Example)
```javascript
// React Auth0 integration
import { useAuth0 } from '@auth0/auth0-react';

const LoginButton = () => {
  const { loginWithRedirect, getAccessTokenSilently } = useAuth0();
  
  const handleLogin = async () => {
    await loginWithRedirect();
    const token = await getAccessTokenSilently();
    
    // Send to PHP API
    const response = await fetch('https://your-server.com/API/v1/auth/validate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ access_token: token })
    });
    
    const data = await response.json();
    if (data.success) {
      localStorage.setItem('jwt_token', data.token);
    }
  };
  
  return <button onClick={handleLogin}>Login with SSO</button>;
};
```

---

## Database Schema for SSO

Add these columns to your Users table if not present:

```sql
ALTER TABLE Users ADD COLUMN IF NOT EXISTS Auth0ID VARCHAR(255);
ALTER TABLE Users ADD COLUMN IF NOT EXISTS OktaID VARCHAR(255);
ALTER TABLE Users ADD COLUMN IF NOT EXISTS AzureID VARCHAR(255);
ALTER TABLE Users ADD COLUMN IF NOT EXISTS ProfilePicture VARCHAR(500);
ALTER TABLE Users ADD COLUMN IF NOT EXISTS LastLogin DATETIME;
ALTER TABLE Users ADD COLUMN IF NOT EXISTS UserType ENUM('customer', 'admin', 'dealer') DEFAULT 'customer';

-- Create index for faster lookups
CREATE INDEX idx_users_email ON Users(Email);
CREATE INDEX idx_users_sso ON Users(Auth0ID, OktaID, AzureID);
```

---

## Environment Variables Summary

Add to your `.env` file based on chosen provider:

### Auth0
```ini
AUTH0_DOMAIN=your-tenant.auth0.com
AUTH0_CLIENT_ID=xxxxxxxxxxxxx
AUTH0_CLIENT_SECRET=xxxxxxxxxxxxx
AUTH0_CALLBACK_URL=https://your-react-app.com/callback
AUTH0_AUDIENCE=https://your-api-identifier
```

### Okta
```ini
OKTA_ISSUER=https://your-domain.okta.com/oauth2/default
OKTA_CLIENT_ID=xxxxxxxxxxxxx
OKTA_REDIRECT_URI=https://your-react-app.com/callback
```

### Azure AD
```ini
AZURE_TENANT_ID=xxxxx-xxxx-xxxx
AZURE_CLIENT_ID=xxxxx-xxxx-xxxx
AZURE_REDIRECT_URI=https://your-react-app.com/callback
```

---

## Security Best Practices

1. **Always use HTTPS** - SSO tokens should never be transmitted over HTTP
2. **Validate token expiry** - Check `exp` claim
3. **Verify token signature** - Use provider's public keys
4. **Check audience** - Ensure token is for your application
5. **Rate limit** - Prevent brute force attempts
6. **Log authentication events** - For security auditing

---

## Troubleshooting SSO

### "Invalid token" error
- Check token hasn't expired
- Verify correct public keys
- Ensure audience matches

### "User not created" in database
- Check database permissions
- Verify Users table structure
- Check for SQL errors in logs

### CORS errors
- Add SSO provider domain to allowed origins
- Ensure preflight requests handled

### Session not persisting
- Check session_start() called
- Verify session cookie settings
- Check session storage path writable

---

## Migration from Existing Login

To migrate from existing email/password to SSO:

1. **Phase 1**: Add SSO as option, keep existing login
2. **Phase 2**: Encourage SSO adoption with prompts
3. **Phase 3**: Migrate users, provide password reset
4. **Phase 4**: Disable old login, SSO only

---

## Support Resources

- Auth0 Documentation: https://auth0.com/docs
- Okta Developer: https://developer.okta.com
- Azure AD: https://docs.microsoft.com/azure/active-directory
- JWT Debugger: https://jwt.io

This completes the SSO integration guide for PHP 5.6!