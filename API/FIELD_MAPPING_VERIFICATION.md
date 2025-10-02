# Database Field Mapping Verification

## ✅ CORRECT Database Column Names (Based on Original Files)

### Items Table
```sql
- id              (NOT ItemID, item_id, or product_id)
- item_title      (NOT name, title, or product_name) 
- Price           (NOT item_price, price - capital P)
- ImageFile       (NOT image_file, image, or image_url)
- Category        (NOT category_id or CategoryID)
- FormID          (NOT sku, item_number, or SKU)
- Color           (NOT colors or color)
- Size            (NOT sizes or size)
- Description     (NOT description - capital D)
- QtyPrice        (NOT qty_price or quantity_pricing)
- ShowImage       (NOT show_image)
- CID             (NOT cid, client_id, or ClientID)
- Active          (NOT active, is_active, or status)
```

### Category Table
```sql
- ID              (NOT id, category_id, or CategoryID)
- Name            (NOT name or category_name)
- ParentID        (NOT parent_id or parent)
- Status          (NOT status or active)
- CID             (NOT cid or client_id)
- display_type    (NOT DisplayType)
- sort_order      (NOT SortOrder)
```

### Users Table
```sql
- ID              (NOT id or user_id)
- Budget          (NOT budget)
- BudgetBalance   (NOT budget_balance)
```

### FormCategoryLink Table
```sql
- FormID          (links to Items.id)
- CategoryID      (links to Category.ID)
```

## 🔴 COMMON MISTAKES FOUND IN API FILES

1. **cart/add.php** - Was using:
   - `ItemID` instead of `id` ❌
   - `item_price` instead of `Price` ❌

2. **Variable Casing Issues**:
   - Database uses mixed case: `Price`, `ImageFile`, `FormID`
   - PHP variables should match exactly

3. **ID Field Confusion**:
   - Items table uses lowercase `id`
   - Category table uses uppercase `ID`
   - Users table uses uppercase `ID`

## ✅ FILES ALREADY CORRECTED

1. **products/list.php** ✅
   - Fixed column mapping
   - Added CORS headers

2. **products/detail.php** ✅
   - Fixed column mapping
   - Added CORS headers

3. **categories/list.php** ✅
   - Already using correct fields
   - Added CORS headers

4. **cart/get.php** ✅
   - Added CORS headers

5. **cart/add.php** ✅
   - Fixed: `ItemID` → `id`
   - Fixed: `item_price` → `Price`

## 📋 TEST VERIFICATION SCRIPT

Upload `test-database-columns.php` to verify actual column names:
```bash
curl https://dentwizard.lgstore.com/lg/API/v1/test-database-columns.php
```

This will show the exact column names from your database.

## 🚨 CRITICAL NOTES

1. **Case Sensitivity**: MySQL column names ARE case-sensitive on Linux servers!
   - `Price` is NOT the same as `price`
   - `ImageFile` is NOT the same as `imagefile`

2. **Active Field Values**: Can be:
   - 1 (integer)
   - 'Y' (string)
   - 'yes' (string)

3. **Image Path Structure**: Always `/pdf/{CID}/{ImageFile}`
   - Example: `/pdf/56/product.jpg`

## 📦 UPLOAD THESE FILES

All files in: `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\`

1. `/config/cors.php` - CORS configuration
2. `/v1/products/list.php` - Products listing
3. `/v1/products/detail.php` - Product details
4. `/v1/categories/list.php` - Categories listing
5. `/v1/cart/get.php` - Get cart
6. `/v1/cart/add.php` - Add to cart
7. `/v1/test-database-columns.php` - Database verification script

## 🧪 VERIFICATION STEPS

1. Upload `test-database-columns.php` first
2. Run it to verify column names match this document
3. If columns differ, let me know and I'll update all files
4. Then upload the corrected API files