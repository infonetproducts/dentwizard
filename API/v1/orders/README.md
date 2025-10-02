# Orders API Files

## Files in this folder:

### 1. orders_test_simple.php
- **Use this FIRST for testing**
- Simple version that just returns basic order list
- Only 63 lines, easy to debug
- Test with: `/lg/API/v1/orders/my-orders`

### 2. orders_final.php  
- **Use this after test version works**
- Full functionality (list, details, create orders)
- Complete order management
- Endpoints:
  - List: `/lg/API/v1/orders/my-orders`
  - Detail: `/lg/API/v1/orders/123`

### 3. list.php & create.php (existing)
- Your original files (different framework)
- Keep for reference

## Deployment Instructions:

1. **Test First:**
   - Upload `orders_test_simple.php` to server as `/lg/API/v1/orders.php`
   - Test: `https://dentwizard.lgstore.com/lg/API/v1/orders/my-orders`
   - Should return JSON array of orders

2. **Deploy Full Version:**
   - If test works, upload `orders_final.php` as `/lg/API/v1/orders.php`
   - This replaces the test version

## Framework Notes:

These new files match your working `detail.php` pattern:
- Uses mysqli (not PDO)
- Direct database connection
- Same header setup
- Same error handling
- Adds session authentication for user orders

## Database Credentials:

```php
$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';
```

## Troubleshooting:

- **"Not logged in"** - User session not active
- **Empty array** - User has no orders in database
- **Connection failed** - Check database credentials
- **404 error** - Check file is at correct path on server
