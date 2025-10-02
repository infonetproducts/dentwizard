# Cart Solution for Development & Production

## 🏠 Local Development (Will Work Now)
With the proxy configured in package.json:
- All API calls to `/lg/API/v1/*` get proxied to `https://dentwizard.lgstore.com/lg/API/v1/*`
- PHP sessions will work because everything appears same-origin
- No CORS issues

## 🚀 Production on Render (Requires Additional Setup)

### Option A: Environment Variables (Simplest)
1. Set in Render environment:
   ```
   REACT_APP_API_URL=https://dentwizard.lgstore.com/lg/API/v1
   ```

2. Update cart.php on server to handle CORS:
   ```php
   header("Access-Control-Allow-Origin: https://your-app.onrender.com");
   header("Access-Control-Allow-Credentials: true");
   ```

### Option B: LocalStorage Cart (Most Reliable)
Create a cart that works everywhere without sessions:

```javascript
// Local cart that syncs to server only when needed
const localCart = {
  add: (item) => {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.push(item);
    localStorage.setItem('cart', JSON.stringify(cart));
  },
  get: () => JSON.parse(localStorage.getItem('cart') || '[]'),
  sync: async () => {
    // Send to server at checkout
    const cart = localCart.get();
    await api.post('/checkout/prepare', { items: cart });
  }
};
```

### Option C: Cart Tokens (Most Scalable)
Instead of PHP sessions, use database storage with tokens.

## 📋 What You Need to Do:

### For Development (Now):
1. **Restart your React app** to apply the proxy:
   ```bash
   npm start
   ```

2. **Test the cart** - it should work with sessions now!

### For Render Deployment:
1. **Choose a strategy** (A, B, or C above)
2. **Update your .env** for production
3. **Configure CORS** on your PHP server

## 🎯 Recommended Approach

**For immediate fix:** Use the proxy (already configured)
**For production:** Use LocalStorage cart (works everywhere, no session issues)

Would you like me to implement the LocalStorage solution so it works both locally and on Render?