# DentWizard LG Store API Specifications
Version 1.0 - Created: September 2025

## Environment & Infrastructure

### PHP Configuration
- **PHP Version**: 5.6 (Legacy system)
- **Server**: Apache with mod_php or FastCGI
- **Important Note**: `getallheaders()` function may not be available in FastCGI configuration

### Database Configuration
- **Database Type**: MySQL/MariaDB
- **Database Name**: rwaf
- **Username**: rwaf
- **Password**: Py*uhb$L$##
- **Host**: localhost
- **Character Set**: UTF-8
- **Table Name Case Sensitivity**: Linux/Unix servers are case-sensitive (Users not users, Orders not orders)

### API Base URL Structure
- **Development**: http://localhost:3000/lg/API/v1/
- **Production**: https://dentwizard.lgstore.com/lg/API/v1/

## Database Connection Pattern

### CORRECT Pattern (Object-Oriented mysqli)
```php
<?php
// Database connection - ALWAYS use this pattern
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = @new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die(json_encode(array('success' => false, 'message' => 'Database connection failed')));
}

// Query example
$result = $mysqli->query("SELECT * FROM Users WHERE id = $user_id");
$data = $result->fetch_assoc();

// Escape strings
$escaped = $mysqli->real_escape_string($input);

// Insert ID
$id = $mysqli->insert_id;

// Close connection
$mysqli->close();
```

### INCORRECT Pattern (Do NOT use)
```php
// DO NOT USE procedural mysqli
$conn = mysqli_connect(...);  // WRONG
mysqli_query($conn, ...);      // WRONG
```

## Standard API Headers

### All API Files Must Include
```php
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token, X-User-Id");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}
```

## Authentication Pattern

### Token-Based Authentication
Since headers may not work with the Express proxy, authentication is passed in the request body:

```php
// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Get authentication from request body
$auth_token = isset($input['auth_token']) ? $input['auth_token'] : '';
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;

if (!$auth_token || !$user_id) {
    die(json_encode(array('success' => false, 'error' => 'Unauthorized')));
}
```

### Alternative Header Authentication (when available)
```php
function getUserIdFromRequest() {
    foreach ($_SERVER as $key => $value) {
        if ($key == 'HTTP_X_USER_ID') {
            return $value;
        }
    }
    return null;
}
```

## Database Schema

### Key Tables (Case-Sensitive Names)

#### Users Table
- **Table Name**: Users (capital U)
- **Key Fields**:
  - id (int)
  - name (varchar)
  - email (varchar)
  - BudgetBalance (decimal)
  - Budget (decimal)

#### Orders Table
- **Table Name**: Orders (capital O)
- **Key Fields**:
  - id (int, auto-increment)
  - order_id (varchar) - Format: "mmdd-HHMMSS-userid"
  - user_id (int)
  - OrderDate (datetime)
  - order_status (varchar)
  - order_total (decimal)
  - shipping_charge (decimal)
  - total_sale_tax (decimal)
  - payment_method (varchar)
  - ship_name, ship_add1, ship_add2, ship_city, ship_state, ship_zip
  - CID (int) - Client ID (244 for DentWizard)

#### OrderItems Table
- **Table Name**: OrderItems (not OrderDetails)
- **Key Fields**:
  - OrderRecordID (int) - Links to Orders.id
  - ID (int) - Always set to 1
  - ItemID (int) - Product ID
  - FormID (varchar) - Format: "mmddYYYY_HHMMSS"
  - FormDescription (varchar) - Product name
  - Quantity (int)
  - Price (decimal)
  - **Attribute Fields**:
    - size_item (varchar)
    - color_item (varchar)
    - artwork_logo (varchar)

#### Items Table (Products)
- **Table Name**: Items
- **Key Fields**:
  - ID (int)
  - item_title (varchar)
  - Price (decimal)
  - ImageFile (varchar)
  - CID (int) - Client ID filter
  - status_item (char) - 'Y' for active

## API Endpoints

### Products
- `/products/list.php` - Get product listing
- `/products/detail.php?id={product_id}` - Get product details
- `/products/categories.php` - Get categories

### User
- `/user/profile.php` - Get user profile (uses token auth)
- `/user/login.php` - User login
- `/user/addresses.php` - Get user addresses

### Cart
- `/cart/get.php` - Get cart items
- `/cart/add.php` - Add item to cart
- `/cart/remove.php` - Remove item from cart
- `/cart/clear.php` - Clear cart

### Orders
- `/orders/create.php` - Create new order
- `/orders/my-orders.php?user_id={id}` - Get user's orders
- `/orders/order-detail.php?id={order_id}` - Get order details

### Budget
- `/budget/get.php` - Get user's budget info
- `/budget/check.php` - Check if order amount within budget

## Standard Response Formats

### Success Response
```json
{
    "success": true,
    "data": {...},
    "message": "Operation successful"
}
```

### Error Response
```json
{
    "success": false,
    "error": "Error message here"
}
```

### List Response
```json
{
    "success": true,
    "data": [...],
    "total": 100,
    "page": 1,
    "per_page": 20
}
```

## Order Creation Flow

### 1. Order ID Generation
```php
$order_id = date("md-His") . "-" . $user_id;
// Example: "0928-224025-20296"
```

### 2. Order Insertion
```php
$order_sql = "INSERT INTO Orders SET
    order_id = '$order_id',
    user_id = $user_id,
    OrderDate = NOW(),
    order_status = 'new',
    order_total = $subtotal,
    shipping_charge = $shipping_cost,
    total_sale_tax = $tax,
    CID = 244,
    ...";
```

### 3. OrderItems Insertion with Attributes
```php
$item_sql = "INSERT INTO OrderItems SET
    OrderRecordID = $order_record_id,
    ID = 1,
    ItemID = $product_id,
    FormID = '$form_id',
    FormDescription = '$product_name',
    Quantity = $quantity,
    Price = $price,
    size_item = '$size',
    color_item = '$color',
    artwork_logo = '$artwork',
    ...";
```

### 4. Budget Update
```php
if ($payment_method === 'budget') {
    $new_budget = $current_budget - $total;
    $mysqli->query("UPDATE Users SET BudgetBalance = $new_budget WHERE id = $user_id");
}
```

## React Integration

### API Service Configuration (React)
```javascript
const API_BASE_URL = '/lg/API/v1';

// Request with authentication
const response = await fetch(`${API_BASE_URL}/orders/create.php`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        auth_token: localStorage.getItem('authToken'),
        user_id: localStorage.getItem('userId'),
        items: cartItems,
        shippingAddress: {...},
        ...
    })
});
```

## Common Issues & Solutions

### Issue 1: Headers Not Received
**Problem**: Express proxy doesn't forward custom headers
**Solution**: Pass authentication in request body instead of headers

### Issue 2: Table Names Case Sensitivity
**Problem**: Linux servers are case-sensitive for table names
**Solution**: Always use exact case (Users not users, Orders not orders)

### Issue 3: Product Attributes Not Saving
**Problem**: Attributes (size, color, logo) not being saved to OrderItems
**Solution**: Ensure React sends these fields in the items array and PHP extracts them:
```php
$size = isset($item['size']) ? $mysqli->real_escape_string($item['size']) : '';
$color = isset($item['color']) ? $mysqli->real_escape_string($item['color']) : '';
$artwork = isset($item['artwork']) ? $mysqli->real_escape_string($item['artwork']) : '';
```

### Issue 4: Empty/Blank Pages
**Problem**: PHP errors not displaying
**Solution**: Check mysqli connection syntax - must use object-oriented style

## Testing Checklist

### For New API Endpoints
1. ✅ Use correct mysqli object syntax
2. ✅ Include proper CORS headers
3. ✅ Handle OPTIONS requests
4. ✅ Use correct table names (case-sensitive)
5. ✅ Escape all user inputs with `$mysqli->real_escape_string()`
6. ✅ Return JSON responses with success/error structure
7. ✅ Test with both header and body authentication

### For Order Creation
1. ✅ Generate proper order_id format
2. ✅ Insert into Orders table with all required fields
3. ✅ Insert into OrderItems with size_item, color_item, artwork_logo
4. ✅ Update user's BudgetBalance
5. ✅ Use transaction with rollback on error
6. ✅ Return order_id and order_number in response

## File Organization

```
/lg/API/v1/
├── auth/
│   ├── login.php
│   └── check.php
├── budget/
│   └── get.php
├── cart/
│   ├── add.php
│   ├── get.php
│   ├── remove.php
│   └── clear.php
├── orders/
│   ├── create.php
│   ├── my-orders.php
│   └── order-detail.php
├── products/
│   ├── list.php
│   ├── detail.php
│   └── categories.php
└── user/
    ├── profile.php
    └── addresses.php
```

## Version History
- v1.0 (Sept 2025): Initial documentation based on existing system analysis

---
End of API Specifications Document