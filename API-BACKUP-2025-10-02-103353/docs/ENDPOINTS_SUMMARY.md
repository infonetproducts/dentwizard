# Complete API Endpoints Summary

## Authentication & User Management

### Authentication
- `POST /v1/auth/login.php` - User login with email/password
- `POST /v1/auth/logout.php` - Logout and destroy session
- `GET /v1/auth/check.php` - Check authentication status
- `POST /v1/auth/reset-password.php` - Request password reset

### User Management
- `POST /v1/user/register.php` - Create new user account
- `GET /v1/user/profile.php` - Get user profile data
- `PUT /v1/user/profile.php` - Update user profile
- `GET /v1/user/addresses.php` - Get user addresses
- `POST /v1/user/addresses.php` - Add new address
- `PUT /v1/user/addresses/{id}.php` - Update address
- `DELETE /v1/user/addresses/{id}.php` - Delete address

## Products & Categories

### Products
- `GET /v1/products/list.php` - List all products with filters
- `GET /v1/products/detail.php?id={id}` - Get single product details
- `GET /v1/products/search.php?q={query}` - Search products
- `GET /v1/products/featured.php` - Get featured products
- `GET /v1/products/best-sellers.php` - Get best selling products
- `POST /v1/products/sale-price.php` - Calculate product sale price

### Categories
- `GET /v1/categories/list.php` - List all categories
- `GET /v1/categories/tree.php` - Get category hierarchy
- `GET /v1/categories/{id}/products.php` - Get products by category

## Shopping Cart

### Cart Management
- `GET /v1/cart/get.php` - Get current cart contents
- `POST /v1/cart/add.php` - Add item to cart
- `PUT /v1/cart/update.php` - Update cart item quantity
- `DELETE /v1/cart/remove.php` - Remove item from cart
- `POST /v1/cart/clear.php` - Clear entire cart
- `GET /v1/cart/count.php` - Get cart item count

### Cart Discounts
- `POST /v1/cart/apply-discount.php` - Apply discount to cart
- `POST /v1/cart/remove-discount.php` - Remove discount from cart

## Discounts & Promotions

### Gift Cards
- `POST /v1/giftcard/validate.php` - Validate gift card and check balance
- `POST /v1/giftcard/purchase.php` - Purchase new gift card

### Coupons & Promo Codes
- `POST /v1/coupon/validate.php` - Validate promo/coupon code

## Orders & Checkout

### Checkout Process
- `POST /v1/checkout/calculate.php` - Calculate totals, tax, shipping
- `POST /v1/checkout/validate.php` - Validate checkout data
- `POST /v1/checkout/process.php` - Process order submission
- `POST /v1/checkout/confirm.php` - Confirm order after payment

### Order Management
- `GET /v1/orders/list.php` - Get user's order history
- `GET /v1/orders/detail.php?id={id}` - Get order details
- `GET /v1/orders/tracking.php?id={id}` - Get tracking information
- `POST /v1/orders/cancel.php` - Cancel order (if allowed)

## Payment Processing

### Payment Methods
- `GET /v1/payment/methods.php` - Get available payment methods
- `POST /v1/payment/process.php` - Process payment
- `POST /v1/payment/verify.php` - Verify payment status

### PayPal Integration
- `POST /v1/payment/paypal/create.php` - Create PayPal order
- `POST /v1/payment/paypal/capture.php` - Capture PayPal payment

## Shipping & Tax

### Shipping
- `POST /v1/shipping/calculate.php` - Calculate shipping rates
- `GET /v1/shipping/methods.php` - Get available shipping methods
- `POST /v1/shipping/validate-address.php` - Validate shipping address

### Tax
- `POST /v1/tax/calculate.php` - Calculate tax for order

## Customer Service

### Contact & Support
- `POST /v1/contact/submit.php` - Submit contact form
- `GET /v1/support/faqs.php` - Get FAQs

### Reviews & Ratings
- `GET /v1/reviews/product/{id}.php` - Get product reviews
- `POST /v1/reviews/submit.php` - Submit product review

## Search & Filters

### Search
- `GET /v1/search/products.php` - Advanced product search
- `GET /v1/search/suggestions.php` - Search suggestions/autocomplete

## Store Information

### Store Details
- `GET /v1/store/info.php` - Get store information
- `GET /v1/store/locations.php` - Get physical locations
- `GET /v1/store/policies.php` - Get store policies

## Inventory Management

### Stock
- `GET /v1/inventory/check.php?product_id={id}` - Check product availability
- `POST /v1/inventory/reserve.php` - Reserve items during checkout

## Wishlist/Favorites

### Wishlist Management
- `GET /v1/wishlist/get.php` - Get user's wishlist
- `POST /v1/wishlist/add.php` - Add item to wishlist
- `DELETE /v1/wishlist/remove.php` - Remove from wishlist

## Analytics & Tracking

### User Analytics
- `POST /v1/analytics/track.php` - Track user events
- `POST /v1/analytics/page-view.php` - Track page views

## Admin API (Future)

### Product Management
- `POST /v1/admin/products/create.php` - Create product
- `PUT /v1/admin/products/update.php` - Update product
- `DELETE /v1/admin/products/delete.php` - Delete product

### Order Management
- `GET /v1/admin/orders/list.php` - List all orders
- `PUT /v1/admin/orders/status.php` - Update order status

### User Management
- `GET /v1/admin/users/list.php` - List all users
- `PUT /v1/admin/users/update.php` - Update user details

### Discount Management
- `POST /v1/admin/discounts/create.php` - Create discount code
- `PUT /v1/admin/discounts/update.php` - Update discount
- `DELETE /v1/admin/discounts/delete.php` - Delete discount

## Response Format

All endpoints return JSON with this structure:
```json
{
    "success": true/false,
    "data": {}, // Response data
    "message": "Success or error message",
    "errors": [] // Array of error messages if applicable
}
```

## Authentication Headers

For protected endpoints, include:
```
Authorization: Bearer {token}
```
or rely on PHP session authentication

## Rate Limiting

- 1000 requests per hour per IP for authenticated users
- 100 requests per hour per IP for unauthenticated users
- Discount validation: 10 attempts per code per hour

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Server Error

## CORS Configuration

Configured for React frontend at:
- Development: `http://localhost:3000`
- Production: Your production domain

## Session Management

- Sessions persist for 24 hours
- Cart data stored in session
- Discount codes stored in session
- User preferences in session

## Notes

1. All POST requests require CSRF token
2. File uploads use multipart/form-data
3. Dates in ISO 8601 format
4. Prices in decimal format (10.99)
5. IDs as integers

## Testing Endpoints

Use tools like:
- Postman
- cURL
- Thunder Client (VS Code)
- Your React app's API service

Example cURL:
```bash
curl -X POST https://yourdomain.com/api/v1/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```