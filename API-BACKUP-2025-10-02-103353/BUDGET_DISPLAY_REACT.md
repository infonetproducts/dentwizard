# Budget Display Implementation for React Frontend

## How Budget is Displayed Throughout the Site

### 1. Header Component (Always Visible)
The budget status should be displayed in the header on every page.

```jsx
// components/BudgetDisplay.jsx
import React, { useEffect, useState } from 'react';
import { apiService } from '../services/api';

const BudgetDisplay = () => {
  const [budgetStatus, setBudgetStatus] = useState(null);
  
  useEffect(() => {
    // Fetch budget status on mount and periodically
    fetchBudgetStatus();
    const interval = setInterval(fetchBudgetStatus, 60000); // Refresh every minute
    return () => clearInterval(interval);
  }, []);
  
  const fetchBudgetStatus = async () => {
    try {
      const response = await apiService.getBudgetStatus();
      setBudgetStatus(response.data.data);
    } catch (error) {
      console.error('Failed to fetch budget status');
    }
  };
  
  if (!budgetStatus || !budgetStatus.has_budget) {
    return null; // Don't show if no budget
  }
  
  const getStatusColor = () => {
    switch(budgetStatus.status) {
      case 'critical': return 'text-red-600 bg-red-100';
      case 'warning': return 'text-yellow-600 bg-yellow-100';
      default: return 'text-green-600 bg-green-100';
    }
  };
  
  return (
    <div className={`budget-display ${getStatusColor()} px-3 py-1 rounded-lg`}>
      <span className="font-semibold">Budget: </span>
      <span>{budgetStatus.display_text}</span>
      {budgetStatus.percentage_used >= 90 && (
        <span className="ml-2 text-sm">⚠️ Low Budget</span>
      )}
    </div>
  );
};
```

### 2. Product List Page
Show how products affect budget when adding to cart.

```jsx
// components/ProductCard.jsx
const ProductCard = ({ product, userBudget }) => {
  const [quantity, setQuantity] = useState(1);
  
  const calculateBudgetImpact = () => {
    const itemTotal = product.price * quantity;
    const budgetAfter = userBudget.budget_balance - itemTotal;
    return {
      canAfford: itemTotal <= userBudget.budget_balance,
      budgetAfter,
      itemTotal
    };
  };
  
  const budgetImpact = userBudget ? calculateBudgetImpact() : null;
  
  return (
    <div className="product-card">
      {/* Product details */}
      
      {userBudget && userBudget.has_budget && (
        <div className="budget-impact mt-2">
          <div className="text-sm text-gray-600">
            Item Total: ${budgetImpact.itemTotal.toFixed(2)}
          </div>
          {!budgetImpact.canAfford ? (
            <div className="text-red-600 text-sm font-semibold">
              ⚠️ Exceeds available budget
            </div>
          ) : (
            <div className="text-green-600 text-sm">
              ✓ Budget after: ${budgetImpact.budgetAfter.toFixed(2)}
            </div>
          )}
        </div>
      )}
      
      <button 
        disabled={userBudget && !budgetImpact.canAfford}
        className={`add-to-cart ${!budgetImpact?.canAfford ? 'opacity-50' : ''}`}
      >
        Add to Cart
      </button>
    </div>
  );
};
```

### 3. Shopping Cart Page
Display budget status and warnings.

```jsx
// pages/Cart.jsx
const Cart = () => {
  const [cart, setCart] = useState(null);
  const [budgetStatus, setBudgetStatus] = useState(null);
  
  useEffect(() => {
    fetchCart();
  }, []);
  
  const fetchCart = async () => {
    const response = await apiService.getCart();
    setCart(response.data.data);
    setBudgetStatus(response.data.data.budget_status);
  };
  
  return (
    <div className="cart-page">
      {/* Cart items */}
      
      {cart?.budget && cart.budget.has_budget && (
        <div className="budget-summary border rounded p-4 mb-4">
          <h3 className="font-bold mb-2">Budget Information</h3>
          <div className="grid grid-cols-2 gap-2">
            <div>Current Balance:</div>
            <div className="font-semibold">
              ${cart.budget.budget_balance.toFixed(2)}
            </div>
            
            <div>Cart Total:</div>
            <div className="font-semibold">
              ${cart.cart_summary.total.toFixed(2)}
            </div>
            
            <div>After Order:</div>
            <div className={`font-semibold ${
              budgetStatus.within_budget ? 'text-green-600' : 'text-red-600'
            }`}>
              {budgetStatus.within_budget 
                ? `$${budgetStatus.balance_after.toFixed(2)}`
                : `Over by $${budgetStatus.shortage.toFixed(2)}`
              }
            </div>
          </div>
          
          {!budgetStatus.within_budget && (
            <div className="mt-3 p-3 bg-red-100 text-red-700 rounded">
              <strong>⚠️ Budget Exceeded</strong>
              <p>{budgetStatus.message}</p>
              <p>Please remove items to proceed with checkout.</p>
            </div>
          )}
        </div>
      )}
      
      <button 
        onClick={proceedToCheckout}
        disabled={!budgetStatus?.can_checkout}
        className={`checkout-btn ${!budgetStatus?.can_checkout ? 'opacity-50' : ''}`}
      >
        Proceed to Checkout
      </button>
    </div>
  );
};
```

### 4. Checkout Page
Final budget confirmation before order.

```jsx
// pages/Checkout.jsx
const Checkout = () => {
  const [budgetCheck, setBudgetCheck] = useState(null);
  const [orderTotal, setOrderTotal] = useState(0);
  
  useEffect(() => {
    checkBudget();
  }, [orderTotal]);
  
  const checkBudget = async () => {
    const response = await apiService.checkBudget({
      order_total: orderTotal,
      include_shipping: true,
      shipping_cost: 10.00
    });
    setBudgetCheck(response.data.data);
  };
  
  return (
    <div className="checkout-page">
      {budgetCheck?.has_budget && (
        <div className="budget-final-check">
          <div className="bg-gray-100 p-4 rounded mb-4">
            <h3 className="font-bold mb-2">Budget Summary</h3>
            <table className="w-full">
              <tbody>
                <tr>
                  <td>Available Budget:</td>
                  <td className="text-right font-semibold">
                    ${budgetCheck.budget_balance.toFixed(2)}
                  </td>
                </tr>
                <tr>
                  <td>Order Total:</td>
                  <td className="text-right font-semibold">
                    ${budgetCheck.order_total.toFixed(2)}
                  </td>
                </tr>
                <tr className="border-t pt-2">
                  <td>Remaining After Order:</td>
                  <td className={`text-right font-bold ${
                    budgetCheck.can_proceed ? 'text-green-600' : 'text-red-600'
                  }`}>
                    ${budgetCheck.balance_after_order.toFixed(2)}
                  </td>
                </tr>
              </tbody>
            </table>
            
            {!budgetCheck.can_proceed && (
              <div className="mt-3 p-2 bg-red-100 text-red-700 rounded">
                {budgetCheck.message}
              </div>
            )}
          </div>
        </div>
      )}
      
      <button 
        onClick={submitOrder}
        disabled={budgetCheck && !budgetCheck.can_proceed}
      >
        Place Order
      </button>
    </div>
  );
};
```

### 5. Order Confirmation
Show budget deduction after successful order.

```jsx
// pages/OrderConfirmation.jsx
const OrderConfirmation = ({ orderId }) => {
  const [budgetAfter, setBudgetAfter] = useState(null);
  
  useEffect(() => {
    fetchUpdatedBudget();
  }, []);
  
  return (
    <div className="order-confirmation">
      <h1>Order Confirmed!</h1>
      
      {budgetAfter && (
        <div className="budget-updated bg-blue-100 p-4 rounded">
          <h3>Budget Updated</h3>
          <p>Your new budget balance is: 
            <strong> ${budgetAfter.budget_balance.toFixed(2)}</strong>
          </p>
          <p>You have used {budgetAfter.percentage_used}% of your annual budget.</p>
        </div>
      )}
    </div>
  );
};
```

## API Service Integration

```javascript
// services/api.js
export const apiService = {
  // Budget endpoints
  getBudgetStatus: () => api.get('/budget/status.php'),
  getBudgetFull: () => api.get('/user/budget.php'),
  checkBudget: (data) => api.post('/budget/check.php', data),
  
  // Cart with budget
  getCart: () => api.get('/cart/get.php'), // Returns cart with budget info
  
  // User profile with budget
  getProfile: () => api.get('/user/profile.php'), // Includes budget details
};
```

## Budget Display States

### Visual Indicators
```css
/* Budget status colors */
.budget-good { /* 0-74% used */
  color: #10b981; /* green */
}

.budget-warning { /* 75-89% used */
  color: #f59e0b; /* yellow */
}

.budget-critical { /* 90-100% used */
  color: #ef4444; /* red */
  animation: pulse 2s infinite;
}

.budget-exceeded {
  color: #991b1b;
  background: #fee2e2;
  font-weight: bold;
}
```

## Mobile Display
On mobile, show condensed budget in header:

```jsx
// Mobile header
<div className="mobile-budget-display">
  {budgetStatus.has_budget && (
    <div className="text-sm">
      💰 ${budgetStatus.budget_balance.toFixed(0)}
    </div>
  )}
</div>
```

## Key Features

1. **Always Visible**: Budget displayed in header on every page
2. **Real-time Updates**: Refreshes after cart changes
3. **Visual Warnings**: Color-coded status (green/yellow/red)
4. **Prevention**: Disables checkout if over budget
5. **Calculations**: Shows impact before adding to cart
6. **Mobile Friendly**: Condensed display on small screens

## Implementation Priority

1. ✅ Header budget display (Phase 1)
2. ✅ Cart page budget check (Phase 1)  
3. ✅ Checkout validation (Phase 1)
4. ⭕ Product page impact (Phase 2)
5. ⭕ Budget history page (Phase 2)

This ensures users always know their budget status and can't accidentally exceed their limits!