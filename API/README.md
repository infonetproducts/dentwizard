# DentWizard API Documentation

## Overview
This API serves the DentWizard e-commerce React application, providing product listings, details, and category information. Built with PHP 5.6 compatibility in mind.

## Server Configuration
- **PHP Version:** 5.6
- **Database:** MySQL
- **Base URL:** `https://dentwizard.lgstore.com/lg/API/v1/`

## Database Configuration
```php
$db_host = 'localhost';
$db_name = 'rwaf';
$db_user = 'rwaf';
$db_pass = 'Py*uhb$L$##';  // MUST use single quotes to prevent PHP variable interpretation
```

### Critical Note on Password
The password contains `$` characters. **Always use single quotes** around the password string. Using double quotes will cause PHP to interpret `$L` as a variable, resulting in "Undefined variable" errors.

## Database Tables

### 1. Items Table
Main product information table
- `ID` - Product ID
- `item_title` - Product name
- `Price` - Product price
- `ImageFile` - Main product image filename
- `FormID` - Product SKU (e.g., BCK01168)
- `Description` - Product description
- `CID` - Client ID (244 for DentWizard)
- `status_item` - 'Y' for active products

### 2. item_group_options Table
Stores color variants and other product options
- `option_id` - Unique identifier for the option
- `group_id` - Groups related options together
- `item_id` - Links to Items.ID
- `display_name` - Display name (e.g., "Atlas", "Polished")
- `value` - Option value
- `color_image` - Image filename for this color variant
- `price` - Price modifier (0 = same as base price)
- `CID` - Client ID

### 3. ItemsSizesStyles Table
Stores available sizes for products
- `ItemID` - Links to Items.ID
- `Size` - Size value (e.g., "LT", "XLT", "2LT", "3LT")

### 4. FormCategoryLink Table
Links products to categories
- `FormID` - Links to Items.ID (NOT Items.FormID!)
- `CategoryID` - Category identifier

## API Endpoints

### 1. /products/list.php
Lists all products with optional category filtering

**Parameters:**
- `page` (optional) - Page number for pagination (default: 1)
- `limit` (optional) - Items per page (default: 20)
- `client_id` (optional) - Client ID (default: 244)
- `category_id` (optional) - Filter by category

**Response Structure:**
```json
{
  "status": "success",
  "products": [...],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_items": 100
  }
}
```

### 2. /products/detail.php
Gets detailed information for a single product

**Parameters:**
- `id` (required) - Product ID

**Response Structure:**
```json
{
  "status": "success",
  "data": {
    "id": 91754,
    "name": "Product Name",
    "price": 65.00,
    "image_url": "https://dentwizard.lgstore.com/pdf/244/image.png",
    "image_file": "image.png",
    "sku": "BCK01168",
    "description": "Product description with \\r\\n line breaks",
    "available_sizes": ["LT", "XLT", "2LT", "3LT"],
    "color_variants": [
      {
        "id": 73020,
        "name": "Atlas",
        "value": "Atlas",
        "image": "https://dentwizard.lgstore.com/pdf/244/atlas-image.png",
        "price": 65
      },
      {
        "id": 73021,
        "name": "Polished",
        "value": "Polished",
        "image": "https://dentwizard.lgstore.com/pdf/244/polished-image.png",
        "price": 65
      }
    ]
  }
}
```

### 3. /products/categories_list.php
Lists all product categories

**Response Structure:**
```json
{
  "status": "success",
  "categories": [
    {
      "id": 1,
      "name": "Category Name",
      "product_count": 25
    }
  ]
}
```

## Color Variants Implementation

Color variants are **NOT** stored as separate products. Instead:

1. One product entry exists in the `Items` table
2. Color options are stored in `item_group_options` table
3. Each color has its own image and can have different pricing
4. Colors are retrieved with: 
```sql
SELECT * FROM item_group_options 
WHERE item_id = ? AND CID = 244 AND price = 0
```

### Important Discovery
Initially, we searched for color variants by looking for similar FormIDs (e.g., BCK01168-Atlas, BCK01168-Polished), but these don't exist as separate products. The colors are stored as options in the `item_group_options` table.

## Common Issues and Solutions

### Issue 1: Password Variable Interpretation
**Problem:** Getting "Undefined variable: L" errors
**Solution:** Use single quotes around password string
```php
// WRONG - PHP interprets $L as variable
$pass = "Py*uhb$L$##";  

// CORRECT - Single quotes prevent variable interpretation
$pass = 'Py*uhb$L$##';
```

### Issue 2: CORS Headers
**Problem:** API calls blocked by CORS policy
**Solution:** Use complete CORS headers matching list.php
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}
```

### Issue 3: Error Reporting
**Problem:** PHP errors displayed in JSON response
**Solution:** Disable error reporting in production
```php
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_level()) ob_end_clean();
```

### Issue 4: Database Connection
**Problem:** Connection failures with mysqli
**Solution:** Use consistent connection pattern
```php
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}
```

## Working Code Patterns

### Pattern 1: Safe Query Execution
```php
// For simple queries with integer IDs
$product_id = intval($_GET['id']);
$sql = "SELECT * FROM Items WHERE ID = $product_id AND CID = 244";

// For complex queries with strings
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
```

### Pattern 2: Image URL Construction
```php
// Always prepend the full URL path
$image_url = 'https://dentwizard.lgstore.com/pdf/244/' . $row['ImageFile'];
```

### Pattern 3: Size Detection with Fallback
```php
// 1. Try ItemsSizesStyles table first
// 2. If empty, check FormID patterns
// 3. If still empty, provide defaults based on product name
if (stripos($product['item_title'], 'Tall') !== false || 
    stripos($product['item_title'], 'Big') !== false) {
    $sizes = array('LT', 'XLT', '2LT', '3LT');
} else {
    $sizes = array('S', 'M', 'L', 'XL', '2XL', '3XL');
}
```

## React Integration

The React app expects:
- API responses in `{ status: 'success', data: {...} }` format
- Color variants as an array with id, name, value, image, and price
- Sizes as an array of strings
- Descriptions may contain `\\r\\n` which React converts to line breaks

## Testing

### Quick API Test
```html
<!-- Test any API endpoint -->
<script>
fetch('https://dentwizard.lgstore.com/lg/API/v1/products/detail.php?id=91754')
  .then(r => r.json())
  .then(data => console.log(data));
</script>
```

### Verify Color Variants
Product 91754 should return:
- Atlas variant (ID: 73020)
- Polished variant (ID: 73021)

## File Structure
```
/lg/API/v1/
├── products/
│   ├── list.php           # Product listing
│   ├── detail.php          # Product details with colors
│   └── categories_list.php # Category listing
```

## Important Notes

1. **PHP 5.6 Compatibility:** Use mysqli or PDO, not mysqli object-oriented style extensively
2. **Client ID:** Always filter by CID = 244 for DentWizard
3. **Status Check:** Only show products where status_item = 'Y'
4. **Color Variants:** Always check item_group_options table, not separate products
5. **FormID vs ID:** FormCategoryLink uses Items.ID, not Items.FormID

## Debugging Tips

1. **Check Raw API Response:** Visit the API URL directly in browser
2. **Verify Database Connection:** Test with simple query first
3. **Check PHP Error Logs:** Look for syntax errors if getting 500 errors
4. **Test CORS:** Use browser developer tools Network tab
5. **Validate JSON:** Use jsonlint.com to verify response format

## Last Updated
January 17, 2025 - Added color variants functionality from item_group_options table