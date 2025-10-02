# API Handoff Requirements - Updated with All Features

## What We Need from Developer to Start React Development

### 1. Database Table Names Verification
Since your PHP app is working, we need to know your actual table names:

```sql
-- Run these queries and provide the results:
SHOW TABLES;

-- Specifically looking for tables related to:
-- • Products/Items
-- • Users (with budget fields)
-- • Gift cards/certificates
-- • Promo codes/coupons
-- • Orders and order items
-- • Budget logs
```

### 2. Column Name Mapping
For key tables, provide column names:

```sql
-- Users table (especially budget fields)
DESCRIBE Users;

-- Products/Items table
DESCRIBE Items;

-- Gift cards table (whatever it's called)
DESCRIBE [your_gift_card_table];

-- Promo codes table
DESCRIBE [your_promo_code_table];
```

### 3. API Configuration Completed

Confirm these files are configured:
```
✓ .env file created with database credentials
✓ CORS enabled for React development (localhost:3000)
✓ API accessible at: https://_______________/API/v1
```

### 4. Test These Core Endpoints

Run these tests and provide the output:

```bash
# 1. System check (should show database connected)
curl https://your-api-url.com/API/v1/test.php

# 2. Products list (should return products)
curl "https://your-api-url.com/API/v1/products/list.php?client_id=1&limit=2"

# 3. Budget status (if user logged in)
curl -H "Authorization: Bearer TOKEN" \
     https://your-api-url.com/API/v1/budget/status.php

# 4. Test a promo code (if you have test codes)
curl -X POST https://your-api-url.com/API/v1/coupon/validate.php \
     -H "Content-Type: application/json" \
     -d '{"promo_code":"TESTCODE","order_total":100}'
```

### 5. Working Features Checklist

Mark which features are working with your existing data:

**Core Commerce:**
- [ ] Products list endpoint returns items
- [ ] Product prices display correctly
- [ ] Cart add/remove functions work
- [ ] Categories endpoint returns categories

**Budget System:**
- [ ] Users table has Budget and BudgetBalance columns
- [ ] Budget displays in user profile
- [ ] Budget check works at checkout
- [ ] Budget transaction logs exist

**Discount System:**
- [ ] Gift card validation works
- [ ] Promo code validation works
- [ ] Sale prices calculate correctly
- [ ] Discounts apply to cart

**Authentication:**
- [ ] JWT tokens generate
- [ ] Session variables work
- [ ] SSO configured (if using)

### 6. Session Variable Confirmation

Confirm these session variables are used in your PHP:
```php
// Cart
$_SESSION['Order']              // Cart items
$_SESSION['size_item']          // Selected sizes
$_SESSION['color_item']         // Selected colors

// Discounts
$_SESSION['promo_code_str']     // Active promo
$_SESSION['gift_card_code']     // Active gift card
$_SESSION['dealer_code']        // Dealer discount

// User
$_SESSION['user_id'] or $_SESSION['AID']
$_SESSION['client_id'] or $_SESSION['CID']
```

### 7. Test Data Available

Provide test data for development:
```
Test User Account:
- Email: ________________
- Password: _____________
- Has Budget: Yes/No
- Budget Amount: $_______

Test Promo Code:
- Code: _________________
- Discount: _____________

Test Gift Card:
- Code: _________________
- Balance: $_____________

Test Product IDs:
- Regular product: ______
- Sale product: _________
```

### 8. API Response Samples

Provide actual JSON responses from your API:

```javascript
// 1. Products list response
{
  // Paste actual response here
}

// 2. User profile with budget
{
  // Paste actual response here
}

// 3. Cart with discounts
{
  // Paste actual response here
}
```

## Minimum Required to Start

### Phase 1 - Can Start Immediately With:
- ✓ API URL
- ✓ Products endpoint working
- ✓ CORS enabled

### Phase 2 - Needed Within 48 Hours:
- ✓ Database table/column names verified
- ✓ Budget endpoints tested
- ✓ Discount validation working
- ✓ Test accounts created

### Phase 3 - Before Production:
- ✓ All endpoints verified
- ✓ SSO configured
- ✓ Performance optimized

## Developer Verification Script

Create and run this PHP script to verify everything:

```php
<?php
// verify-api.php
require_once 'config/database.php';

$checks = array();

// 1. Database connection
try {
    $pdo = getPDOConnection();
    $checks['database'] = 'Connected';
} catch (Exception $e) {
    $checks['database'] = 'Failed: ' . $e->getMessage();
}

// 2. Check Users table for budget
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM Users LIKE 'Budget%'");
    $budget_cols = $stmt->fetchAll();
    $checks['budget_columns'] = count($budget_cols) > 0 ? 'Found' : 'Missing';
} catch (Exception $e) {
    $checks['budget_columns'] = 'Error: ' . $e->getMessage();
}

// 3. Check for gift cards table
$tables = array('gift_cards', 'GiftCards', 'gift_certificates', 'GiftCertificates');
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        $checks['gift_cards_table'] = $table;
        break;
    } catch (Exception $e) {
        $checks['gift_cards_table'] = 'Not found with standard names';
    }
}

// 4. Check for promo codes table
$tables = array('promo_codes', 'PromoCodes', 'coupons', 'Coupons');
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        $checks['promo_codes_table'] = $table;
        break;
    } catch (Exception $e) {
        $checks['promo_codes_table'] = 'Not found with standard names';
    }
}

// 5. Test product query
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Items WHERE Active = 1");
    $result = $stmt->fetch();
    $checks['active_products'] = $result['count'] . ' products';
} catch (Exception $e) {
    $checks['products'] = 'Error: ' . $e->getMessage();
}

// Output results
echo "API Verification Results:\n";
echo "========================\n\n";

foreach ($checks as $check => $result) {
    echo str_pad($check, 20) . ": " . $result . "\n";
}

// List all tables
echo "\nAll Tables in Database:\n";
echo "----------------------\n";
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "- " . $row[0] . "\n";
}
?>
```

Run this and send us the output!

## Delivery Format

Email or message with:

```
Subject: API Ready for React Development

1. API URL: https://_______________/API/v1

2. Database Verified:
   - Gift cards table is called: _________
   - Promo codes table is called: ________
   - Budget columns confirmed: Yes/No

3. Working Endpoints:
   ✓ /test.php - System check works
   ✓ /products/list.php - Returns [X] products
   ✓ /budget/status.php - Shows budget
   ✓ /coupon/validate.php - Validates codes
   ✓ /giftcard/validate.php - Checks balance
   ✓ /cart/get.php - Returns cart with discounts

4. Test Accounts:
   Email: test@example.com
   Password: ********
   Budget: $5000 (Balance: $3500)
   
5. Test Codes:
   Promo: SAVE20 (20% off)
   Gift Card: ABCD-1234-5678-9012 ($50 balance)

6. Known Issues:
   - [List any problems]

7. Table Name Differences:
   - Using 'GiftCertificates' instead of 'gift_cards'
   - Using 'cert_code' instead of 'code' column
   - [List other differences]

Available for questions: [Phone/Email/Slack]
```

## What Happens Next

Once we receive this information:

1. **Day 1**: Update API queries to match your table names
2. **Day 1-2**: Build React components with real data
3. **Day 2-3**: Integrate authentication flow
4. **Day 3-5**: Complete checkout process
5. **Day 5-7**: Testing and optimization

## Critical Information Needed

**Most Important**: Your actual database table and column names for:
- Gift cards/certificates
- Promo codes/coupons  
- Budget fields in Users
- Sale price fields in Items

Without this, the API queries won't work with your database!

## Questions to Answer

1. **What is your gift card table actually called?**
2. **What is your promo code table actually called?**
3. **Do Users have Budget and BudgetBalance columns?**
4. **What are your session variable names for cart and user?**
5. **Is there a test environment or only production?**

---

**Bottom Line**: The API code is complete but needs to be connected to your actual database tables. Once you provide the table/column names, the API will work immediately with your existing data!