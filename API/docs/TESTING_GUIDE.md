# API Testing Guide

## Quick Start Testing

### 1. Verify Your Database Connection
First, update `/config/database.php` with your actual database credentials:
```php
define('DB_HOST', 'your_host');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 2. Test Basic Connectivity
```bash
curl https://yourdomain.com/api/v1/products/list.php
```

## Testing Discount Features

### Test Gift Card Validation
```bash
# Find an existing gift card in your database first
curl -X POST https://yourdomain.com/api/v1/giftcard/validate.php \
  -H "Content-Type: application/json" \
  -d '{"gift_card_code":"YOUR-ACTUAL-GIFT-CODE"}'
```

### Test Promo Code Validation
```bash
# Use an active promo code from your database
curl -X POST https://yourdomain.com/api/v1/coupon/validate.php \
  -H "Content-Type: application/json" \
  -d '{"promo_code":"YOURCODE","order_total":100.00}'
```

### Test Discount Application
```bash
# Apply a discount to cart
curl -X POST https://yourdomain.com/api/v1/cart/apply-discount.php \
  -H "Content-Type: application/json" \
  -d '{"discount_type":"promo_code","code":"SUMMER20","cart_total":150.00}'
```

## Complete Test Flow

### 1. Authentication Test
```javascript
// Login
const loginTest = async () => {
    const response = await fetch('https://yourdomain.com/api/v1/auth/login.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            email: 'test@example.com',
            password: 'password123'
        })
    });
    const data = await response.json();
    console.log('Login:', data);
};
```

### 2. Product Browsing Test
```javascript
// Get products
const getProducts = async () => {
    const response = await fetch('https://yourdomain.com/api/v1/products/list.php');
    const data = await response.json();
    console.log('Products:', data);
};
```

### 3. Cart Operations Test
```javascript
// Add to cart
const addToCart = async (productId, quantity) => {
    const response = await fetch('https://yourdomain.com/api/v1/cart/add.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity,
            options: {}
        })
    });
    const data = await response.json();
    console.log('Cart Add:', data);
};
```

### 4. Discount Application Test
```javascript
// Full discount test flow
const testDiscounts = async () => {
    // 1. Check sale price
    const saleResponse = await fetch('https://yourdomain.com/api/v1/products/sale-price.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            product_id: 123,
            regular_price: 99.99,
            category_id: 5
        })
    });
    console.log('Sale Price:', await saleResponse.json());
    
    // 2. Validate promo code
    const promoResponse = await fetch('https://yourdomain.com/api/v1/coupon/validate.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            promo_code: 'TESTCODE',
            order_total: 100.00
        })
    });
    console.log('Promo Valid:', await promoResponse.json());
    
    // 3. Apply to cart
    const applyResponse = await fetch('https://yourdomain.com/api/v1/cart/apply-discount.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            discount_type: 'promo_code',
            code: 'TESTCODE',
            cart_total: 100.00
        })
    });
    console.log('Applied:', await applyResponse.json());
    
    // 4. Check gift card
    const giftResponse = await fetch('https://yourdomain.com/api/v1/giftcard/validate.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            gift_card_code: 'GIFT-CARD-CODE-HERE'
        })
    });
    console.log('Gift Card:', await giftResponse.json());
};
```

### 5. Checkout Test
```javascript
// Complete checkout flow
const testCheckout = async () => {
    // 1. Calculate totals
    const calcResponse = await fetch('https://yourdomain.com/api/v1/checkout/calculate.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            shipping_method: 'standard',
            shipping_zip: '90210'
        })
    });
    console.log('Calculation:', await calcResponse.json());
    
    // 2. Process order
    const orderResponse = await fetch('https://yourdomain.com/api/v1/checkout/process.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            payment_method: 'credit_card',
            shipping_address: {
                name: 'John Doe',
                street: '123 Main St',
                city: 'Los Angeles',
                state: 'CA',
                zip: '90210'
            }
        })
    });
    console.log('Order:', await orderResponse.json());
};
```

## Database Verification Commands

Run these SQL commands to verify your table structure:

```sql
-- Check gift card tables
SHOW TABLES LIKE '%gift%';
SHOW TABLES LIKE '%certificate%';

-- Check promo/coupon tables  
SHOW TABLES LIKE '%promo%';
SHOW TABLES LIKE '%coupon%';
SHOW TABLES LIKE '%discount%';

-- Check for dealer codes
SHOW TABLES LIKE '%dealer%';

-- Once you find the tables, check columns
DESCRIBE your_gift_table_name;
DESCRIBE your_promo_table_name;

-- Look for active codes to test with
SELECT * FROM your_promo_table LIMIT 5;
SELECT * FROM your_gift_table WHERE status = 'active' LIMIT 5;
```

## Postman Collection Setup

### Create Environment Variables
```json
{
  "base_url": "https://yourdomain.com/api",
  "email": "test@example.com",
  "password": "password123",
  "token": "",
  "cart_id": "",
  "product_id": "1"
}
```

### Test Sequence
1. **Login** → saves token
2. **Get Products** → saves product_id
3. **Add to Cart** → saves cart_id
4. **Apply Discount** → verify discount
5. **Checkout Calculate** → check totals
6. **Process Order** → complete purchase

## Common Testing Issues

### Issue: "Table not found"
**Fix:** Update table names in API endpoints
```php
// Find in endpoint files and update:
$stmt = $pdo->prepare("SELECT * FROM your_actual_table_name WHERE ...");
```

### Issue: "Column not found"
**Fix:** Check actual column names
```php
// Update column references:
$balance = $row['your_actual_balance_column'];
```

### Issue: Session not persisting
**Fix:** Ensure session_start() is called
```php
// Should be at top of each endpoint:
session_start();
```

### Issue: CORS errors
**Fix:** Update CORS headers in endpoints
```php
header('Access-Control-Allow-Origin: http://localhost:3000');
```

## Testing Checklist

### Phase 1: Basic Setup
- [ ] Database connection works
- [ ] Can retrieve products
- [ ] Authentication works
- [ ] Sessions persist

### Phase 2: Cart Operations
- [ ] Add items to cart
- [ ] Update quantities
- [ ] Remove items
- [ ] Cart persists in session

### Phase 3: Discounts
- [ ] Gift cards validate
- [ ] Promo codes apply
- [ ] Discounts calculate correctly
- [ ] Session stores discount info

### Phase 4: Checkout
- [ ] Shipping calculates
- [ ] Tax calculates
- [ ] Order processes
- [ ] Confirmation works

### Phase 5: Integration
- [ ] React app connects
- [ ] All flows work end-to-end
- [ ] Error handling works
- [ ] Performance acceptable

## Load Testing

### Simple Load Test
```bash
# Test 100 concurrent requests
for i in {1..100}; do
  curl https://yourdomain.com/api/v1/products/list.php &
done
```

### Apache Bench
```bash
# 1000 requests, 10 concurrent
ab -n 1000 -c 10 https://yourdomain.com/api/v1/products/list.php
```

## Security Testing

### Test Input Validation
```bash
# SQL injection attempt (should fail)
curl -X POST https://yourdomain.com/api/v1/auth/login.php \
  -d "email=test' OR '1'='1&password=test"

# XSS attempt (should be sanitized)
curl -X POST https://yourdomain.com/api/v1/products/search.php \
  -d "q=<script>alert('xss')</script>"
```

## Debugging Tips

### Enable Error Reporting
```php
// Add to config/config.php for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Log SQL Queries
```php
// Add to database.php
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

### Check Session Data
```php
// Create test endpoint: /v1/debug/session.php
session_start();
header('Content-Type: application/json');
echo json_encode($_SESSION);
```

## React Integration Test

```javascript
// Complete API service test
import axios from 'axios';

const API_BASE = 'https://yourdomain.com/api/v1';

const apiService = {
    // Test all endpoints
    async runFullTest() {
        try {
            // 1. Login
            const loginRes = await axios.post(`${API_BASE}/auth/login.php`, {
                email: 'test@example.com',
                password: 'password123'
            });
            console.log('✅ Login successful');
            
            // 2. Get products
            const productsRes = await axios.get(`${API_BASE}/products/list.php`);
            console.log('✅ Products loaded');
            
            // 3. Add to cart
            const cartRes = await axios.post(`${API_BASE}/cart/add.php`, {
                product_id: productsRes.data.data[0].id,
                quantity: 1
            });
            console.log('✅ Added to cart');
            
            // 4. Apply discount
            const discountRes = await axios.post(`${API_BASE}/cart/apply-discount.php`, {
                discount_type: 'promo_code',
                code: 'TEST10',
                cart_total: 100
            });
            console.log('✅ Discount applied');
            
            // 5. Checkout
            const checkoutRes = await axios.post(`${API_BASE}/checkout/calculate.php`, {
                shipping_method: 'standard'
            });
            console.log('✅ Checkout calculated');
            
            console.log('🎉 All tests passed!');
            
        } catch (error) {
            console.error('❌ Test failed:', error.response?.data || error.message);
        }
    }
};

// Run test
apiService.runFullTest();
```

## Support & Troubleshooting

If tests fail:
1. Check error logs in `/logs/error.log`
2. Verify database connection
3. Check table and column names match
4. Ensure proper PHP version (7.0+)
5. Verify file permissions
6. Check PHP extensions (PDO, JSON, Session)