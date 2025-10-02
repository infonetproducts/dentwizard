# IMPORTANT: Tax Code Implementation for PA Clothing Exemption

## The Problem
Pennsylvania does NOT tax clothing/apparel, but our system isn't sending the proper tax codes to TaxJar, so all items are being taxed.

## TaxJar Product Tax Codes for Apparel
- **20010** - Clothing (general apparel, tax-exempt in PA)
- **20041** - Children's clothing
- **20070** - Footwear
- **20110** - Costumes

## Required Changes

### 1. Database Schema Update
Add `tax_code` field to products table:
```sql
ALTER TABLE products ADD COLUMN tax_code VARCHAR(20) DEFAULT '20010';
UPDATE products SET tax_code = '20010' WHERE category IN ('apparel', 'clothing', 'shirts', 'polos');
```

### 2. PHP Backend Updates

#### In products.php API:
```php
// When fetching products, include tax_code
$products = $db->query("SELECT product_id, product_name, price, tax_code FROM products");
```

#### In cart.php API:
```php
// Include tax_code in cart items
$cartItems[] = [
    'product_id' => $row['product_id'],
    'product_name' => $row['product_name'],
    'price' => $row['price'],
    'quantity' => $row['quantity'],
    'tax_code' => $row['tax_code'] ?? '20010'  // Default to clothing
];
```

### 3. React App Updates

#### Update Cart Redux (cartSlice.js):
The cart items should include tax_code from the API.

#### Update CheckoutPage.js:
Replace the empty taxCode with actual product tax codes.

### 4. Tax Calculation Fix
The tax_calculate.php needs to properly handle product_tax_code for each line item.

## Immediate Fix (Without Database Changes)

Since all your products are apparel, we can hardcode the tax code for now: