# Gift Cards, Coupons & Discounts API Documentation

## Important: Using Existing Database Tables
**No new tables needed!** Your PHP application already has working gift cards, promo codes, and discounts. This API connects to your existing database tables. Your developer just needs to:
1. Verify actual table names in your database
2. Update SQL queries to match your column names  
3. Test with your existing data

## Overview
Complete discount system implementation matching your existing PHP e-commerce platform functionality.

## Discount Types Supported

### 1. Gift Cards/Certificates
- **Existing Table**: Check your database for gift certificates table
- **Format**: XXXX-XXXX-XXXX-XXXX (16 characters)
- **Features**: Partial usage, balance tracking, 1-year expiry
- **Session Storage**: `$_SESSION['gift_card_code']`, `$_SESSION['gift_discount_amount']`

### 2. Promo Codes
- **Existing Table**: Check your database for promo/coupon codes table
- **Types**: Percentage, fixed amount, free shipping
- **Features**: Usage limits, minimum orders, date ranges, category/user targeting
- **Session Storage**: `$_SESSION['promo_code_str']`, `$_SESSION['set_promo_code_discount']`

### 3. Dealer Codes (B2B)
- **Existing Table**: Check your database for dealer discounts table
- **Features**: Special B2B pricing, credit tracking
- **Session Storage**: `$_SESSION['dealer_code']`, `$_SESSION['set_dealer_discount']`

### 4. Automatic Sale Pricing
- **Location**: Likely in your Items/Products table
- **Levels**: Product-specific, category-wide, site-wide
- **Features**: Date-based activation, best price automatically applied

## API Endpoints

### Gift Card Validation
```
POST /v1/giftcard/validate.php
```
**Request:**
```json
{
    "gift_card_code": "ABCD-EFGH-IJKL-MNOP"
}
```
**Response:**
```json
{
    "success": true,
    "valid": true,
    "balance": 50.00,
    "original_amount": 100.00,
    "expiry_date": "2025-12-31"
}
```

### Apply Discount to Cart
```
POST /v1/cart/apply-discount.php
```
**Request:**
```json
{
    "discount_type": "promo_code",
    "code": "SUMMER20",
    "cart_total": 150.00,
    "user_id": 12345  // Optional
}
```
**Response:**
```json
{
    "success": true,
    "discount_applied": 30.00,
    "new_total": 120.00,
    "active_discounts": [
        {
            "type": "promo_code",
            "code": "SUMMER20",
            "amount": 30.00
        }
    ]
}
```

### Remove Discount
```
POST /v1/cart/remove-discount.php
```
**Request:**
```json
{
    "discount_type": "promo_code",  // or "all" to remove everything
    "code": "SUMMER20"  // Optional if removing specific discount
}
```

### Purchase Gift Card
```
POST /v1/giftcard/purchase.php
```
**Request:**
```json
{
    "amount": 50.00,
    "recipient_email": "friend@email.com",
    "sender_name": "John Doe",
    "message": "Happy Birthday!",
    "user_id": 12345
}
```

### Check Sale Price
```
POST /v1/products/sale-price.php
```
**Request:**
```json
{
    "product_id": 123,
    "regular_price": 99.99,
    "category_id": 45
}
```
**Response:**
```json
{
    "on_sale": true,
    "sale_price": 79.99,
    "discount_type": "product_sale",
    "discount_amount": 20.00,
    "sale_end_date": "2025-12-31"
}
```

## Implementation Checklist for Developer

### 1. Database Verification
```sql
-- Find your actual table names (examples):
SHOW TABLES LIKE '%gift%';
SHOW TABLES LIKE '%coupon%';
SHOW TABLES LIKE '%promo%';
SHOW TABLES LIKE '%dealer%';

-- Then check column names:
DESCRIBE your_gift_cards_table;
DESCRIBE your_promo_codes_table;
```

### 2. Update SQL Queries
Example updates needed in the API files:
```php
// In /v1/giftcard/validate.php
// Change from assumed name:
$stmt = $pdo->prepare("SELECT * FROM gift_cards WHERE code = :code");

// To your actual table/columns:
$stmt = $pdo->prepare("SELECT * FROM GiftCertificates WHERE cert_number = :code");
```

### 3. Session Variable Mapping
Verify these session variables match your checkout.php:
- `$_SESSION['promo_code_str']` - The promo code string
- `$_SESSION['set_promo_code_discount']` - Promo discount amount
- `$_SESSION['gift_card_code']` - Gift card code
- `$_SESSION['gift_discount_amount']` - Gift card amount applied
- `$_SESSION['dealer_code']` - Dealer code
- `$_SESSION['set_dealer_discount']` - Dealer discount percentage

### 4. Discount Application Order
Based on your checkout_verification.php, discounts apply in this order:
1. Sale prices (automatic)
2. Promo code OR dealer code (not both)
3. Gift card (on remaining balance)
4. Budget check (after all discounts)

## React Integration Examples

### Apply Promo Code
```javascript
const applyPromoCode = async (code) => {
    try {
        const response = await apiService.post('/v1/cart/apply-discount.php', {
            discount_type: 'promo_code',
            code: code,
            cart_total: cartTotal,
            user_id: currentUser?.id
        });
        
        if (response.success) {
            setActiveDiscounts(response.active_discounts);
            setCartTotal(response.new_total);
        }
    } catch (error) {
        console.error('Failed to apply promo code:', error);
    }
};
```

### Gift Card Balance Check
```javascript
const checkGiftCard = async (giftCardCode) => {
    try {
        const response = await apiService.post('/v1/giftcard/validate.php', {
            gift_card_code: giftCardCode
        });
        
        if (response.valid) {
            setGiftCardBalance(response.balance);
            return true;
        }
        return false;
    } catch (error) {
        console.error('Invalid gift card:', error);
        return false;
    }
};
```

### Calculate Final Price with All Discounts
```javascript
const calculateFinalPrice = async (product) => {
    // Check for sale price first
    const saleResponse = await apiService.post('/v1/products/sale-price.php', {
        product_id: product.id,
        regular_price: product.price,
        category_id: product.category_id
    });
    
    let price = saleResponse.on_sale ? saleResponse.sale_price : product.price;
    
    // Apply any active cart-level discounts
    if (activePromoCode) {
        price = price * (1 - promoCodeDiscount);
    }
    
    return price;
};
```

## Testing Checklist

1. **Gift Card Flow**
   - [ ] Validate existing gift card from your database
   - [ ] Apply partial gift card amount
   - [ ] Check remaining balance updates correctly

2. **Promo Code Flow**
   - [ ] Apply percentage discount
   - [ ] Apply fixed amount discount
   - [ ] Check minimum order requirements
   - [ ] Verify usage limit tracking

3. **Discount Stacking**
   - [ ] Sale price + promo code
   - [ ] Sale price + gift card
   - [ ] Verify dealer codes don't stack with promo codes

4. **Session Persistence**
   - [ ] Discounts persist across page refreshes
   - [ ] Discounts carry through to checkout
   - [ ] Session variables match your existing PHP checkout

## Common Issues & Solutions

### Issue: "Table not found" errors
**Solution**: Update table names in API endpoints to match your actual database

### Issue: Gift card balance not updating
**Solution**: Check that column names match (e.g., 'current_balance' vs 'remaining_balance')

### Issue: Discounts not showing in checkout
**Solution**: Verify session variable names match exactly with your checkout.php

### Issue: Promo codes applying multiple times
**Solution**: Check usage tracking logic and session management

## Notes for Future Enhancement
- Consider adding discount audit logging
- Implement admin API for managing promo codes
- Add gift card balance check without applying
- Create bulk gift card generation endpoint
- Add discount analytics endpoints

## Support
For issues with the discount system:
1. Check the actual database table and column names
2. Verify session variables match your PHP application
3. Test with existing data from your live system
4. Review checkout_verification.php for business logic