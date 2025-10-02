# Gift Cards, Coupons & Discounts API Documentation

## Overview
Complete API implementation for your **existing** gift cards, promo codes, dealer codes, and sale pricing systems. This API provides JSON endpoints for features that are already working in your PHP application.

## IMPORTANT: Database Tables
**Your database already has all necessary tables.** The API uses your existing tables for:
- Gift cards/certificates
- Promo codes/coupons
- Dealer codes
- Sale pricing
- Budget tracking

**No new tables needed!** The developer just needs to verify actual table/column names and update the SQL queries to match.

## Features Implemented in API

### 1. Gift Card System

#### Endpoints Created:
- `POST /v1/giftcard/validate.php` - Validate gift card and check balance
- `POST /v1/giftcard/purchase.php` - Purchase a new gift card

#### How Your Gift Cards Work:
Based on your PHP code, your system has:
- Gift card codes with balances
- Partial usage tracking
- Email delivery options
- Custom amounts and messages

#### SQL Queries to Update:
```php
// The API assumes this table name:
"SELECT * FROM gift_cards WHERE code = :code"

// Your actual table might be called:
// - GiftCertificates
// - gift_certificates  
// - tbl_gift_cards
// - Or something else

// Developer needs to check your database and update to:
"SELECT * FROM [YourActualTableName] WHERE [YourCodeColumn] = :code"
```

### 2. Promo Code/Coupon System

#### Endpoints Created:
- `POST /v1/coupon/validate.php` - Validate promo code
- `POST /v1/cart/apply-discount.php` - Apply to cart
- `POST /v1/cart/remove-discount.php` - Remove from cart

#### Your Existing Discount Features:
From your PHP code, I found these discount types:
- Percentage discounts
- Fixed amount off
- Free shipping
- Dealer-specific discounts
- Sale pricing by date

#### Session Variables Your PHP Uses:
```php
// These are from YOUR existing PHP code:
$_SESSION['promo_code_str']           // Active promo code
$_SESSION['set_promo_code_discount']  // Discount amount
$_SESSION['total_price_after_promo_code']
$_SESSION['gift_card_code']
$_SESSION['gift_discount_amount']
$_SESSION['dealer_code']
$_SESSION['set_dealer_discount']
$_SESSION['sale_discount_total']
```

The API maintains these exact session variables for compatibility.

### 3. Automatic Sale Pricing

#### Endpoint Created:
- `GET /v1/products/sale-price.php` - Calculate current sale price

#### Your Sale System:
Based on your `shopping-cart.php` file:
- Products have sale dates (start/end)
- Percentage off or fixed sale prices
- Category-wide sales
- Global site sales

## Database Verification Needed

### Step 1: Find Your Actual Table Names
```sql
-- Run this to see all your tables:
SHOW TABLES;

-- Look for tables containing:
-- 'gift' or 'cert' (for gift cards)
-- 'promo' or 'coupon' or 'discount'
-- 'dealer' (for B2B codes)
```

### Step 2: Check Column Names
Once you identify the tables, check columns:
```sql
-- For gift cards table:
DESCRIBE [your_gift_card_table];

-- For promo codes table:
DESCRIBE [your_promo_code_table];

-- For users (budget):
DESCRIBE Users;
```

### Step 3: Update API Queries
Example updates needed:
```php
// BEFORE (API assumption):
$stmt = $pdo->prepare("
    SELECT * FROM gift_cards 
    WHERE code = :code 
    AND status = 'active'
");

// AFTER (with your actual names):
$stmt = $pdo->prepare("
    SELECT * FROM GiftCertificates 
    WHERE cert_number = :code 
    AND is_active = 1
");
```

## How Discounts Work in Your System

### Cart Flow (From Your PHP Code):
1. Products load with regular or sale prices
2. User can apply promo code via form
3. System stores in session: `$_SESSION['promo_code_str']`
4. Discount calculates and stores: `$_SESSION['set_promo_code_discount']`
5. Gift card can be added: `$_SESSION['gift_card_code']`
6. At checkout, all discounts apply in order

### Discount Priority (From Your Code):
1. **Sale Prices** - Applied automatically when loading products
2. **Promo/Dealer Code** - One per order
3. **Gift Card** - Applied to remaining balance
4. **Budget Check** - After all discounts

## Testing Your Existing Discounts

### 1. Find a Test Promo Code
```sql
-- Look in your promo codes table:
SELECT * FROM [your_promo_table] 
WHERE [status_column] = 'active' 
LIMIT 1;
```

### 2. Find a Test Gift Card
```sql
-- Look in your gift cards table:
SELECT * FROM [your_gift_table] 
WHERE [balance_column] > 0 
LIMIT 1;
```

### 3. Test Via API
```bash
# Test promo code
curl -X POST http://your-server.com/API/v1/coupon/validate.php \
  -H "Content-Type: application/json" \
  -d '{"promo_code":"[YOUR_TEST_CODE]","order_total":100}'

# Test gift card
curl -X POST http://your-server.com/API/v1/giftcard/validate.php \
  -H "Content-Type: application/json" \
  -d '{"gift_card_code":"[YOUR_TEST_CARD]"}'
```

## React Integration

### Display Active Discounts
```jsx
// The API returns all active discounts from session
const getCart = async () => {
  const response = await fetch('/API/v1/cart/get.php');
  const data = await response.json();
  
  // data.active_discounts contains:
  // [
  //   {type: 'promo_code', code: 'SAVE20', amount: 25.00},
  //   {type: 'gift_card', code: 'XXXX-1234', amount: 50.00}
  // ]
};
```

### Apply Discount
```javascript
// Apply any discount type
const applyDiscount = async (type, code) => {
  const response = await fetch('/API/v1/cart/apply-discount.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      discount_type: type, // 'promo_code', 'gift_card', 'dealer_code'
      code: code,
      cart_total: cartTotal
    })
  });
  
  const result = await response.json();
  // Returns updated cart with all discounts
};
```

## Common Table Name Patterns

Based on typical e-commerce databases, look for:

### Gift Cards:
- `gift_cards`, `GiftCards`
- `gift_certificates`, `GiftCertificates`
- `tbl_gift_cards`, `tblGiftCards`
- `certificates`

### Promo Codes:
- `promo_codes`, `PromoCodes`
- `coupons`, `Coupons`
- `discount_codes`, `DiscountCodes`
- `promotions`

### Common Column Names:
- Code: `code`, `cert_code`, `certificate_number`, `promo_code`
- Amount: `amount`, `balance`, `current_balance`, `remaining_amount`
- Status: `status`, `is_active`, `active`, `enabled`

## Troubleshooting

### "Table not found" Error
```sql
-- Find the right table:
SHOW TABLES LIKE '%gift%';
SHOW TABLES LIKE '%cert%';
SHOW TABLES LIKE '%promo%';
SHOW TABLES LIKE '%coupon%';
```

### "Unknown column" Error
```sql
-- Check actual column names:
SHOW COLUMNS FROM [table_name];
```

### Discount Not Applying
Check session variables:
```php
// Add to cart endpoint to debug:
session_start();
error_log("Promo code in session: " . $_SESSION['promo_code_str']);
error_log("Gift card in session: " . $_SESSION['gift_card_code']);
```

## What Your Developer Needs to Do

1. **Identify Your Tables** (10 minutes)
   - Find gift card table name
   - Find promo code table name
   - Note column names

2. **Update SQL Queries** (30 minutes)
   - In `/v1/giftcard/*.php` files
   - In `/v1/coupon/*.php` files
   - In `/v1/cart/apply-discount.php`

3. **Test with Existing Data** (20 minutes)
   - Use a real promo code from your database
   - Use a real gift card with balance
   - Verify discounts calculate correctly

## Key Points

- ✅ Your PHP discount system is **already working**
- ✅ All database tables **already exist**
- ✅ The API just needs table/column names updated
- ✅ Session variables match your existing PHP
- ✅ No data migration needed
- ✅ No new tables needed

## Example: Complete Update Process

```php
// Step 1: Developer checks database
mysql> SHOW TABLES;
// Sees: GiftCertificates, PromoCodesActive, etc.

// Step 2: Checks columns
mysql> DESCRIBE GiftCertificates;
// Sees: cert_id, cert_number, balance, etc.

// Step 3: Updates validate.php
// Changes:
"SELECT * FROM gift_cards WHERE code = :code"
// To:
"SELECT * FROM GiftCertificates WHERE cert_number = :code"

// Step 4: Tests
// Endpoint now works with existing gift cards!
```

## Bottom Line

Your discount system is working in PHP. This API provides JSON endpoints for the React app to use the same features. The developer just needs to:

1. Check what your tables are actually called
2. Update the SQL queries to match
3. Test with your existing discount codes

Time required: About 1 hour to verify and update all discount-related queries!