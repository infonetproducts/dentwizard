// Test file to verify imports
console.log('Checking CheckoutPage imports...');

// These should all resolve correctly now:
const cartSlice = require('../store/slices/cartSlice');
const authSlice = require('../store/slices/authSlice');
const api = require('../services/api');
const taxService = require('../services/taxService');
const shippingService = require('../services/shippingService');
const budgetService = require('../services/budgetService');

console.log('✓ All imports resolved successfully!');
console.log('✓ cartSlice exports clearCart:', typeof cartSlice.clearCart);
console.log('✓ API service available');
console.log('✓ Services loaded');