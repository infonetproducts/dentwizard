# 🔧 API Data Mapping Fix

## The Problem
The API at `/lg/API/v1/products/list.php` is returning empty data because the database columns are not being properly mapped to the expected JSON fields.

## Database Column Names (From Original App)
Based on the original PHP files in `lg_files/`, the database uses these column names:
- `ID` - Product ID
- `item_title` - Product name (NOT `name`)
- `ImageFile` - Image filename (NOT `image_url`)
- `Price` - Product price
- `FormID` - Product SKU/code
- `Description` - Product description
- `CID` - Client/Company ID

## Current API Response (BROKEN)
```json
{
  "id": 3890,
  "name": "",          // ← EMPTY - should map from item_title
  "price": 0,          // ← Sometimes 0 - should map from Price
  "image_url": "",     // ← EMPTY - should map from ImageFile
  "sku": "",          // ← EMPTY - should map from FormID
  "description": "..."
}
```

## Required Fix for products/list.php

The PHP code needs to properly map database columns to JSON fields:

```php
// In your products/list.php file, when building the response:

while ($row = mysqli_fetch_assoc($result)) {
    // Build proper image URL from ImageFile
    $image_url = '';
    if (!empty($row['ImageFile'])) {
        $image_url = "https://dentwizard.lgstore.com/pdf/{$row['CID']}/{$row['ImageFile']}";
    }
    
    $product = [
        'id' => $row['ID'],
        'name' => $row['item_title'],        // ← Map item_title to name
        'price' => floatval($row['Price']),  // ← Map Price to price
        'image_url' => $image_url,           // ← Build full URL from ImageFile
        'category_id' => $row['CategoryID'] ?? 0,
        'sku' => $row['FormID'],            // ← Map FormID to sku
        'description' => $row['Description'],
        'is_active' => ($row['status_item'] == 'Y')
    ];
    
    $products[] = $product;
}
```

## Image Path Structure
Based on the original app, images are stored at:
- Server path: `/pdf/{CID}/{ImageFile}`
- Full URL: `https://dentwizard.lgstore.com/pdf/{CID}/{ImageFile}`

Example: If CID=56 and ImageFile="product123.jpg", the URL should be:
`https://dentwizard.lgstore.com/pdf/56/product123.jpg`

## Categories API Fix
The categories endpoint requires authentication. Either:
1. Remove authentication requirement for categories (make it public)
2. Or provide the React app with proper authentication credentials

## SQL Query Reference (from shop_common_function.php)
```sql
SELECT i.*, 
       CAST(i.category_page_item_order AS SIGNED) as category_page_item_order 
FROM Items i
INNER JOIN FormCategoryLink fcl ON i.ID = fcl.FormID
WHERE i.CID = '$CID' 
  AND i.status_item = 'Y'
  AND fcl.CategoryID IN ('$cat_id')
ORDER BY category_page_item_order ASC
```

## Complete Mapping Table

| Database Column | JSON Field | Notes |
|-----------------|------------|-------|
| ID | id | Product ID |
| item_title | name | Product name |
| ImageFile | image_url | Build full URL with CID |
| Price | price | Convert to float |
| FormID | sku | Product code |
| Description | description | Product description |
| status_item | is_active | 'Y' = true, 'N' = false |
| CID | - | Client ID (used for image path) |

## Testing After Fix

1. Test the API directly:
```bash
curl https://dentwizard.lgstore.com/lg/API/v1/products/list.php?limit=2
```

Should return:
```json
{
  "success": true,
  "data": [{
    "id": 3890,
    "name": "Actual Product Name",
    "price": 29.99,
    "image_url": "https://dentwizard.lgstore.com/pdf/56/image.jpg",
    "sku": "FORM123",
    "description": "Product description"
  }]
}
```

2. Then refresh the React app to see products with names and images.

## Summary for Developer

**The issue:** The API is not mapping database columns correctly. It's looking for columns like `name` but the database uses `item_title`.

**The fix:** Update the PHP code in `/lg/API/v1/products/list.php` to properly map:
- `item_title` → `name`
- `ImageFile` → build full URL for `image_url`
- `FormID` → `sku`
- Ensure `Price` is properly converted to float

Once these mappings are fixed, the React app will display products correctly!
