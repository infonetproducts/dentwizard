# Cart API Upload Instructions

## Files to Upload

Upload these fixed cart API files to your server at `/lg/API/v1/cart/`:

1. **add.php** - Replace with: `add-fixed.php`
2. **get.php** - Replace with: `get-fixed.php`
3. **update.php** - New file (for updating quantities)
4. **remove.php** - New file (for removing items)
5. **clear.php** - New file (for clearing cart)

## Upload Process

1. First, backup your existing cart files on the server
2. Upload these files from your local folder:
   - `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\v1\cart\add-fixed.php` → rename to `add.php`
   - `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\v1\cart\get-fixed.php` → rename to `get.php`
   - `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\v1\cart\update.php`
   - `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\v1\cart\remove.php`
   - `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\v1\cart\clear.php`

## Key Fixes Made

1. **Database Connection**: Now uses direct mysqli connection like the working detail.php
2. **Password Syntax**: Uses single quotes for password to prevent $ interpretation
3. **Error Handling**: Disabled error reporting to prevent JSON corruption
4. **Session Management**: Proper session initialization
5. **Response Format**: Matches what the React app expects

## Testing

After uploading, test with the `test-cart-api.html` file to verify:
1. Add to cart works
2. Get cart shows items
3. Update quantity works
4. Remove item works
5. Clear cart works

## Cart Features

The fixed cart API now supports:
- Adding products with size/color variants
- Viewing cart contents with totals
- Updating item quantities
- Removing individual items
- Clearing entire cart
- Tax calculation (8.25%)
- Shipping calculation (free over $100)