# DentWizard API Specifications
Version: 1.1
Last Updated: September 30, 2025
PHP Version: 5.6

## Overview
The DentWizard API is a RESTful API built with PHP 5.6 that handles authentication, product management, cart operations, and order processing for the DentWizard e-commerce system.

## System Requirements
- PHP 5.6 (CRITICAL - not compatible with PHP 7+)
- MySQL 5.x
- Object-oriented mysqli extension
- CORS support for React frontend

## Database Connection

### Connection Method
The API uses **object-oriented mysqli** (not procedural). This is critical for consistency.

```php
// CORRECT - Object-oriented style (ALWAYS USE THIS)
$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'error' => 'Database connection failed')));
}

// INCORRECT - Procedural style (DO NOT USE)
$conn = mysqli_connect($host, $user, $pass, $db);
```

### Database Credentials
```php
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';  // Note: Special characters in password
$db = 'rwaf';
```

## Standard API Response Format

### Success Response
```json
{
    "success": true,
    "data": { /* response data */ },
    "message": "Operation successful"
}
```

### Error Response
```json
{
    "success": false,
    "error": "Error message",
    "message": "User-friendly error message"
}
```

## CORS Headers (Required for all endpoints)
```php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
```

## Authentication
The system uses a simple token-based authentication with localStorage on the frontend.

### Login Response
```json
{
    "success": true,
    "token": "unique_token",
    "userId": 123,
    "userName": "John Doe",
    "userEmail": "john@example.com"
}
```

### Token Validation
```php
// Get token from request body (not headers due to proxy issues)
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'];

// Validate user
$sql = "SELECT id FROM Users WHERE email = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $userEmail);
```

## Database Schema

### Orders Table
```sql
Orders (
    order_id VARCHAR(255),
    user_id INT,
    order_total DECIMAL(10,2),
    order_date DATETIME,
    Status VARCHAR(50),
    ShipToName VARCHAR(255),
    ShipAddress VARCHAR(255),
    ShipCity VARCHAR(100),
    ShipState VARCHAR(50),
    ShipPostal VARCHAR(20),
    Phone VARCHAR(20),
    PaymentAmount DECIMAL(10,2)
)
```

### OrderItems Table  
```sql
OrderItems (
    id INT AUTO_INCREMENT,
    order_id VARCHAR(255),
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    size_item VARCHAR(50),      -- Product size
    color_item VARCHAR(100),    -- Product color
    artwork_logo VARCHAR(255),   -- Logo/artwork selection
    ID INT DEFAULT 1,           -- Legacy field, always 1
    FormID VARCHAR(255)         -- Format: yyyymmdd_hhmmss
)
```

### Users Table
```sql
Users (  -- Note: Capital U
    id INT AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255),
    password VARCHAR(255),
    budget DECIMAL(10,2)
)
```

## API Endpoints

### 1. Authentication

#### Login
- **URL:** `/lg/API/v1/auth/login.php`
- **Method:** POST
- **Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

### 2. Products

#### Get All Products
- **URL:** `/lg/API/v1/products/list.php`
- **Method:** GET

#### Get Product Detail
- **URL:** `/lg/API/v1/products/detail.php`
- **Method:** GET
- **Params:** `?id=123`

### 3. Cart Operations

#### Add to Cart
- **URL:** `/lg/API/v1/cart/add.php`
- **Method:** POST
- **Body:**
```json
{
    "userEmail": "user@example.com",
    "token": "user_token",
    "productId": 123,
    "quantity": 1,
    "price": 75.00,
    "size": "XL",
    "color": "Navy",
    "artwork": "Dent Wizard Logo"
}
```

#### Get Cart
- **URL:** `/lg/API/v1/cart/get.php`
- **Method:** POST
- **Body:**
```json
{
    "userEmail": "user@example.com",
    "token": "user_token"
}
```

### 4. Orders

#### Create Order
- **URL:** `/lg/API/v1/orders/create.php`
- **Method:** POST
- **Body:**
```json
{
    "userEmail": "user@example.com",
    "token": "user_token",
    "orderTotal": 85.00,
    "shipping": 10.00,
    "shippingInfo": {
        "name": "John Doe",
        "address": "123 Main St",
        "city": "Miami",
        "state": "FL",
        "postal": "33101",
        "phone": "555-1234"
    },
    "items": [
        {
            "product_id": 123,
            "product_name": "Nike Polo",
            "quantity": 1,
            "price": 75.00,
            "size": "XL",
            "color": "Navy",
            "artwork": "Dent Wizard Logo"
        }
    ]
}
```

#### Get User Orders
- **URL:** `/lg/API/v1/orders/my-orders.php`
- **Method:** POST
- **Body:**
```json
{
    "userEmail": "user@example.com",
    "token": "user_token"
}
```

## Code Examples

### Standard PHP Endpoint Template
```php
<?php
// CORS Headers
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database connection
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'error' => 'Database connection failed')));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate token/user
$userEmail = $mysqli->real_escape_string($input['userEmail']);
$token = $mysqli->real_escape_string($input['token']);

// Your logic here...

// Return response
echo json_encode(array(
    'success' => true,
    'data' => $data,
    'message' => 'Success'
));

$mysqli->close();
?>
```

### Prepared Statement Example
```php
$sql = "INSERT INTO Orders (order_id, user_id, order_total) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sid", $order_id, $user_id, $order_total);
$stmt->execute();
$stmt->close();
```

## Common Issues & Solutions

### 1. Database Connection Error Check (CRITICAL)
Always use `connect_error` not `connect_errno`:
```php
// CORRECT - Verified against working code
if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

// INCORRECT - Don't use this
if ($mysqli->connect_errno) {  // Wrong!
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}
```

### 2. Special Characters in Password
The database password contains special characters. Always use single quotes:
```php
$pass = 'Py*uhb$L$##';  // CORRECT
$pass = "Py*uhb$L$##";   // INCORRECT - May cause issues
```

### 3. Table Name Case Sensitivity
MySQL on Linux is case-sensitive for table names:
- Use `Users` not `users`
- Use `OrderItems` not `orderitems`

### 4. PHP Version Compatibility
This system uses PHP 5.6 specific features:
- Array syntax: `array()` not `[]`
- MySQL functions: Use `mysqli` not `PDO`
- JSON: `json_encode()` and `json_decode()` are safe

### 5. CORS and React Integration
- Always include CORS headers
- Handle OPTIONS requests
- Token in request body (not headers) due to proxy issues

### 6. Order ID Format
Order IDs follow the format: `MMDD-HHMMSS-RANDOM`
Example: `0928-224025-20296`

## Testing Endpoints

### Using cURL
```bash
# Test login
curl -X POST http://localhost:3000/lg/API/v1/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Test get products
curl http://localhost:3000/lg/API/v1/products/list.php
```

### Using JavaScript/Fetch
```javascript
// Test order creation
fetch('http://localhost:3000/lg/API/v1/orders/create.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        userEmail: 'test@example.com',
        token: 'user_token',
        orderTotal: 85.00,
        // ... other fields
    })
})
.then(res => res.json())
.then(data => console.log(data));
```

## File Structure
```
/lg/API/v1/
├── auth/
│   ├── login.php
│   └── logout.php
├── products/
│   ├── list.php
│   └── detail.php
├── cart/
│   ├── add.php
│   ├── get.php
│   ├── update.php
│   └── clear.php
├── orders/
│   ├── create.php
│   ├── my-orders.php
│   └── order-detail.php
└── user/
    └── profile.php
```

## Security Considerations

1. **SQL Injection Prevention**
   - Always use prepared statements or `real_escape_string()`
   - Never concatenate user input directly into SQL

2. **Authentication**
   - Validate token on every request
   - Store minimal data in localStorage

3. **CORS**
   - Restrict to specific origins in production
   - Currently set to `http://localhost:3000` for development

4. **Error Handling**
   - Never expose database errors to client
   - Log errors server-side
   - Return generic error messages to client

## Migration Notes

When migrating from the old session-based PHP system to the new React/API system:

1. **Session vs Token Auth**
   - Old: PHP sessions
   - New: Token in localStorage

2. **Data Format**
   - Old: Form data
   - New: JSON

3. **Product Attributes**
   - Must capture: size_item, color_item, artwork_logo
   - Pass through entire order flow
   - Display in order history

## Version History

- **1.0** - Initial API specification
  - PHP 5.6 compatible
  - Object-oriented mysqli
  - Token-based authentication
  - Full order management system

- **1.1** - September 30, 2025
  - **CRITICAL FIX:** Corrected database connection error check
  - Changed from `$mysqli->connect_errno` to `$mysqli->connect_error` (verified against working detail.php)
  - This is the correct method that matches all production code

---

**Important:** Always refer to this document when creating new PHP endpoints or modifying existing ones. Consistency in structure and response format is critical for the React frontend integration.
