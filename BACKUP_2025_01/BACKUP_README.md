# DentWizard Backup - January 2025

## Backup Date
Created: January 2025
Purpose: Complete backup of working e-commerce application

## Backup Contents

### API Files (PHP Backend)
Successfully backed up from `/API/v1/`:

#### Products API
- `detail.php` - Product detail endpoint (WORKING)
- `list.php` - Product listing endpoint (WORKING)
- `list-php56.php` - PHP 5.6 compatible version
- `list-simple.php` - Simplified version
- `detail-fixed.php` - Fixed version with sizes
- `detail-simple.php` - Simplified detail endpoint
- `diagnose.php` - Diagnostic tool
- `test-detail.php` - Test endpoint
- `sale-price.php` - Sale price handler

#### Categories API
- `list.php` - Category hierarchy endpoint (WORKING)
- `list-php56.php` - PHP 5.6 compatible version
- `list-fixed.php` - Fixed hierarchy version

#### Configuration
- `.env` - Database credentials and settings

### React Application Files
Successfully backed up from `/react-app/src/`:

#### Page Components
- `HomePage.js` - Landing page with featured products
- `ProductsPage.js` - Product listing with filters
- `ProductDetailPage.js` - Individual product with size/color selection
- `CartPage.js` - Shopping cart
- `CheckoutPage.js` - Checkout flow
- `LoginPage.js` - User authentication
- `OrderHistoryPage.js` - Order history
- `ProfilePage.js` - User profile

#### Key Components
- `CategoryNav.js` - Horizontal navigation with dropdown menus

#### Redux Store Slices
- `productsSlice.js` - Products and categories state management
- `cartSlice.js` - Shopping cart state
- `authSlice.js` - Authentication state
- `uiSlice.js` - UI state management

## Critical Configuration

### Database Connection
```
Host: localhost
Database: rwaf
Username: rwaf
Password: Py*uhb$L$##
Client ID: 244 (DentWizard)
```

### API Base URL
```
https://dentwizard.lgstore.com/lg/API/v1
```

### React App Settings
```
Port: 3000
Build: npm run build
Start: npm start
```

## File Status

### ✅ Working Files (Production Ready)
- API/products/list.php
- API/products/detail.php
- API/categories/list.php
- react-app/HomePage.js
- react-app/ProductsPage.js
- react-app/ProductDetailPage.js
- react-app/CategoryNav.js

### 🔧 Backup/Alternative Versions
- *-php56.php files - PHP 5.6 compatibility versions
- *-fixed.php files - Bug fix versions
- *-simple.php files - Simplified versions for testing

## Key Features Preserved

1. **Product Display**
   - Grid layout with 4 products per row
   - Taller images (260px height)
   - Proper price formatting ($XX.XX)
   - Category filtering

2. **Navigation**
   - Horizontal category bar
   - Dropdown submenus
   - Mobile responsive
   - No duplicate categories

3. **Product Details**
   - Working product images
   - Size detection from database
   - Color selection
   - Quantity controls
   - Add to cart functionality

4. **Database Integration**
   - Correct column mappings
   - Client-specific filtering (CID=244)
   - Category hierarchy with ParentID
   - FormCategoryLink for product-category relationships

## Restoration Instructions

1. **To Restore API**:
   - Copy API files to `/lg/API/v1/` on server
   - Ensure .env has correct database credentials
   - Set appropriate file permissions

2. **To Restore React App**:
   - Copy react-app files to src directory
   - Run `npm install` to restore dependencies
   - Update .env with correct API URL
   - Run `npm start` for development

3. **Database Requirements**:
   - MySQL with 'rwaf' database
   - Items table with product data
   - Category table with hierarchy
   - FormCategoryLink table for relationships

## Notes
- This backup represents a fully functional state
- All CORS issues resolved
- PHP 5.6 compatibility maintained
- Sizes correctly pulled from database variants
- Images loading from correct paths

## Version Information
- React: 18.2.0
- Material-UI: 5.15.10
- Redux Toolkit: 2.2.1
- PHP: 5.6 (server requirement)
- MySQL: 5.6+

---
Backup created successfully on January 2025
All critical files preserved for disaster recovery