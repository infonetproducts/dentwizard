# API Server Files - Installation Instructions

## 📁 Files to Upload

Upload this entire `v1` folder to your server at `/lg/API/v1/` 

The structure should be:
```
/lg/API/v1/
├── db_config.php
├── products/
│   ├── list.php
│   └── detail.php
├── categories/
│   └── list.php
└── cart/
    ├── get.php
    └── add.php
```

## 🔧 Configuration Steps

### 1. Update Database Configuration

Edit `db_config.php` and update these values with your actual database credentials:

```php
define('DB_HOST', 'localhost');        // Your database host
define('DB_USER', 'your_db_user');      // Your database username
define('DB_PASS', 'your_db_password');  // Your database password
define('DB_NAME', 'your_database_name'); // Your database name
```

Also update the `getCID()` function to return your correct Client ID (CID).

### 2. Verify Database Structure

These files expect the following database structure (based on your original files):

**Items table:**
- ID (primary key)
- item_title (product name)
- ImageFile (image filename)
- Price (product price)
- FormID (SKU/product code)
- Description (product description)
- status_item ('Y' or 'N')
- CID (client/company ID)
- InventoryQuantity
- MinQTY, MaxQTY
- sale_price
- is_enable_sale_item

**FormCategoryLink table:**
- FormID (links to Items.ID)
- CategoryID (category ID)

**categories table (if exists):**
- cat_id (category ID)
- category_name
- parent_category_id
- category_order
- status
- CID

### 3. Test Each Endpoint

After uploading, test each endpoint:

1. **Products List:**
   ```
   https://dentwizard.lgstore.com/lg/API/v1/products/list.php?limit=5
   ```
   Should return products with proper names, prices, and image URLs

2. **Product Detail:**
   ```
   https://dentwizard.lgstore.com/lg/API/v1/products/detail.php?id=3890
   ```
   Replace 3890 with an actual product ID

3. **Categories:**
   ```
   https://dentwizard.lgstore.com/lg/API/v1/categories/list.php
   ```
   Should return category list (or default if no categories table)

4. **Cart:**
   ```
   https://dentwizard.lgstore.com/lg/API/v1/cart/get.php
   ```
   Should return empty cart initially

## ✅ What These Files Fix

### Proper Column Mapping
- `item_title` → `name`
- `ImageFile` → `image_url` (with full URL)
- `FormID` → `sku`
- `Price` → `price`

### CORS Headers
All files include proper CORS headers at the top:
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
```

### Image URLs
Images are properly built as:
```
https://dentwizard.lgstore.com/pdf/{CID}/{ImageFile}
```

## 🧪 Testing from React App

Once uploaded and configured:
1. Open your React app at http://localhost:3011
2. Products should display with names, prices, and images
3. Check browser console for any errors

## ⚠️ Important Notes

1. **Database Connection:** Make sure your database allows connections from the web server
2. **File Permissions:** Ensure PHP files have proper permissions (usually 644)
3. **PHP Version:** These files use mysqli and require PHP 5.6+
4. **Sessions:** Cart functionality uses PHP sessions - ensure sessions are enabled
5. **Image Path:** Images must exist at `/pdf/{CID}/` on your server

## 🐛 Troubleshooting

If products still show empty data:
1. Check database connection in db_config.php
2. Verify column names match your database
3. Check PHP error logs for any issues
4. Test SQL queries directly in phpMyAdmin

If categories don't load:
1. The categories table might not exist - the API will return a default category
2. Check if your categories table has different column names

If images don't show:
1. Verify images exist at `/pdf/{CID}/` on your server
2. Check that ImageFile column contains valid filenames
3. Ensure the CID value is correct

## 📞 Support

If you need to modify column names or table structures, update the SQL queries in each file to match your database schema.

The key is ensuring the JSON response maps database columns to these expected fields:
- id
- name
- price
- image_url
- sku
- description
