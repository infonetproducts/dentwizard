# Missing Features & Endpoints from Original PHP System

## Critical Missing Features Found

### 1. 🔴 Budget Management System (CRITICAL)
Your system has a comprehensive budget tracking system that we completely missed!

**Database Tables Involved:**
- Users table: `Budget`, `BudgetBalance` fields
- `budget_log_all_trans` - Budget transaction logs
- `budget_log_all_trans_detail` - Detailed budget change history

**How it Works:**
- Users have annual budget limits (Budget field)
- Current available budget (BudgetBalance field)
- Budget is deducted when orders are placed
- Budget is restored when orders are cancelled
- All changes are logged for audit trail
- Year-based budget tracking (resets annually)

**Required Endpoints:**
```php
GET  /v1/user/budget.php         # Get user's budget info
POST /v1/user/budget/update.php  # Admin updates budget
GET  /v1/user/budget/history.php # Budget transaction history
POST /v1/budget/check.php        # Check if order fits in budget
```

### 2. 🔴 Gift Cards/Certificates
Found references to gift functionality in the system

**Required Endpoints:**
```php
POST /v1/giftcard/validate.php   # Validate gift card code
POST /v1/giftcard/apply.php      # Apply to order
GET  /v1/giftcard/balance.php    # Check balance
POST /v1/giftcard/purchase.php   # Buy gift card
```

### 3. 🔴 Multi-Address Management
Users can have multiple shipping addresses

**Missing from Profile:**
- ShipToName, ShipToDept
- Multiple address management
- Default shipping address selection

**Required Endpoints:**
```php
GET  /v1/user/addresses.php      # List all addresses
POST /v1/user/addresses/add.php  # Add new address
PUT  /v1/user/addresses/update.php
DELETE /v1/user/addresses/delete.php
POST /v1/user/addresses/default.php # Set default
```

### 4. 🔴 Custom Orders System
Special handling for custom orders with file uploads

**Database Fields:**
- `is_custom_order` flag in Orders table
- Custom order approval workflow
- File upload handling

**Required Endpoints:**
```php
POST /v1/orders/custom/create.php    # Submit custom order
POST /v1/orders/custom/upload.php    # Upload design files
GET  /v1/orders/custom/status.php    # Check approval status
POST /v1/orders/custom/approve.php   # Admin approval
```

### 5. 🔴 Kit Products System
Products can be bundled as kits with special pricing

**Database Fields:**
- `kit_id`, `kit_quantity`, `kit_price` in OrderItems

**Required Endpoints:**
```php
GET  /v1/products/kits.php          # List product kits
GET  /v1/products/kits/detail.php   # Kit components
POST /v1/cart/add-kit.php           # Add entire kit
```

### 6. 🔴 Employee/User Types & Permissions
Different user types with different capabilities

**User Types Found:**
- Regular customers
- Employees (with types)
- Virtual admins
- Dealers

**Required Endpoints:**
```php
GET  /v1/user/permissions.php       # User's permissions
PUT  /v1/user/type.php             # Change user type (admin)
GET  /v1/users/list.php            # List users (admin)
```

### 7. 🔴 Order Request/Approval System
Order request workflow with approvals

**Database:**
- `OrderItems_req` table
- Status tracking (pending/approved/rejected)

**Required Endpoints:**
```php
POST /v1/orders/request.php         # Submit order request
GET  /v1/orders/requests/pending.php # View pending
POST /v1/orders/request/approve.php  # Approve request
POST /v1/orders/request/reject.php   # Reject request
```

### 8. 🔴 Discount/Coupon System
Apply discounts and promotional codes

**Required Endpoints:**
```php
POST /v1/coupon/validate.php        # Check coupon code
POST /v1/coupon/apply.php          # Apply to cart
GET  /v1/coupons/available.php     # User's available coupons
```

### 9. 🟡 TaxJar Integration
Tax calculation through TaxJar API (partially implemented)

**Enhancement Needed:**
- Full TaxJar integration in checkout
- Shipping tax calculations
- Multi-state tax rules

### 10. 🟡 Stripe Payment Integration
Payment processing through Stripe

**Required Endpoints:**
```php
POST /v1/payment/intent.php        # Create payment intent
POST /v1/payment/confirm.php       # Confirm payment
GET  /v1/payment/methods.php       # Saved payment methods
POST /v1/payment/method/add.php    # Save new card
```

### 11. 🟡 Reporting & Analytics
Missing admin reporting features

**Required Endpoints:**
```php
GET /v1/reports/sales.php          # Sales reports
GET /v1/reports/budget-usage.php   # Budget utilization
GET /v1/reports/orders.php         # Order analytics
GET /v1/reports/users.php          # User activity
```

### 12. 🟡 Store Locations
Multiple store location management

**Required Endpoints:**
```php
GET /v1/locations/list.php         # All locations
GET /v1/locations/nearest.php      # Find nearest
GET /v1/locations/inventory.php    # Location inventory
```

## User Profile - Missing Fields

The user profile endpoint needs these additional fields:

```php
// Current /v1/user/profile.php is missing:
{
  "budget": 5000.00,           // Annual budget limit
  "budget_balance": 3250.50,   // Available budget
  "employee_type": "standard",  // Employee classification
  "user_type": "employee",      // User role
  "department": "Sales",        // Department
  "ship_to_name": "John Doe",   // Shipping name
  "ship_to_dept": "Marketing",  // Shipping department
  "multiple_addresses": [...],  // Array of addresses
  "is_virtual_admin": false,    // Virtual admin flag
  "credit_limit": 10000.00,     // Credit limit if applicable
  "payment_terms": "NET30",     // Payment terms
  "tax_exempt": false,          // Tax exemption status
  "company": "ABC Corp",        // Company name
  "created_date": "2023-01-15", // Account creation
  "last_order_date": "2024-03-20", // Last activity
  "total_orders": 45,           // Order count
  "total_spent": 12500.00       // Lifetime value
}
```

## Database Schema Updates Needed

```sql
-- Ensure these tables exist:
CREATE TABLE IF NOT EXISTS budget_log_all_trans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cid INT,
    user_id INT,
    action_title VARCHAR(255),
    log_type VARCHAR(50),
    created_dtm DATETIME
);

CREATE TABLE IF NOT EXISTS budget_log_all_trans_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_id INT,
    field_name VARCHAR(50),
    old_value DECIMAL(10,2),
    new_value DECIMAL(10,2),
    created_dtm DATETIME
);

CREATE TABLE IF NOT EXISTS OrderItems_req (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    OrderRecordID INT,
    ItemID INT,
    status TINYINT, -- 1=pending, 2=approved, 3=rejected
    -- other fields
);

-- Add missing columns to Users table:
ALTER TABLE Users ADD COLUMN IF NOT EXISTS Budget DECIMAL(10,2);
ALTER TABLE Users ADD COLUMN IF NOT EXISTS BudgetBalance DECIMAL(10,2);
ALTER TABLE Users ADD COLUMN IF NOT EXISTS employee_type VARCHAR(50);
ALTER TABLE Users ADD COLUMN IF NOT EXISTS is_virtual_admin CHAR(1) DEFAULT 'N';
```

## Priority Implementation Order

### Phase 1 - CRITICAL (Must have for go-live)
1. ✅ Basic product/cart/checkout (DONE)
2. 🔴 **Budget management system** 
3. 🔴 **User profile with all fields**
4. 🔴 **Multi-address support**

### Phase 2 - HIGH (Week 1 after launch)
5. 🔴 Gift cards
6. 🔴 Discount/coupon system
7. 🟡 Enhanced tax calculation
8. 🟡 Payment processing

### Phase 3 - MEDIUM (Month 1)
9. 🔴 Custom orders
10. 🔴 Kit products
11. 🔴 Order approval workflow
12. 🟡 Reporting

## Impact on React Frontend

The React app will need:

### 1. Budget Display Component
```javascript
// Show budget status on every page
<BudgetStatus 
  budget={5000}
  balance={3250.50}
  percentage={65}
/>
```

### 2. Address Management
```javascript
// Multiple shipping addresses in checkout
<AddressSelector 
  addresses={userAddresses}
  onSelect={handleAddressSelect}
  onAddNew={openAddressModal}
/>
```

### 3. Gift Card/Coupon Input
```javascript
// In checkout
<PromoCodeSection
  onApplyGiftCard={applyGiftCard}
  onApplyCoupon={applyCoupon}
/>
```

### 4. Order Request Flow
```javascript
// For orders requiring approval
<OrderRequestForm
  requiresApproval={true}
  approver={managerEmail}
/>
```

## Estimated Additional Time

- Budget system implementation: 4-6 hours
- Gift cards: 3-4 hours
- Multi-address: 2-3 hours
- Custom orders: 4-5 hours
- Discount system: 3-4 hours
- Full user profile: 2 hours
- **Total additional time: 18-24 hours**

## Critical Questions for Business

1. **Is the budget system mandatory?** Without it, employees can't track spending limits
2. **Are gift cards currently being used?** Need to migrate existing gift card balances
3. **How many users have multiple addresses?** Affects migration complexity
4. **Is the order approval workflow active?** Determines if we need it at launch
5. **Which user types exist?** Need complete list for permissions

## Action Items for Developer

1. Check if Budget fields exist in Users table
2. Verify budget_log tables exist
3. Implement budget check in checkout
4. Add budget endpoints ASAP
5. Update user profile to include all fields
6. Test with users who have budgets

---

**⚠️ CRITICAL: The budget management system is essential for your business model and must be implemented before launch!**