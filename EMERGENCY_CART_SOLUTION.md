# 🚨 EMERGENCY SOLUTION - Single Cart Handler

Since `add.php` keeps failing (possibly server blocking that filename), use this single handler:

## 📁 Upload This File:

**File:** `C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\API\v1\cart\cart.php`
**Upload to:** `/lg/API/v1/cart/cart.php`

## 🔧 How It Works:

Instead of separate files (add.php, get.php, etc.), everything goes through `cart.php`:

- **Add to cart:** POST to `cart.php?action=add`
- **Get cart:** GET to `cart.php?action=get` 
- **Update:** POST to `cart.php?action=update`
- **Clear:** POST to `cart.php?action=clear`

## 🧪 Test It:

First, test manually in browser:
1. `https://dentwizard.lgstore.com/lg/API/v1/cart/cart.php?action=get`
   - Should return cart (empty or with items)

2. Test add with this HTML file:

```html
<!DOCTYPE html>
<html>
<head><title>Test Cart.php</title></head>
<body>
<h1>Test cart.php</h1>
<button onclick="testAdd()">Test Add to Cart</button>
<button onclick="testGet()">Test Get Cart</button>
<pre id="result"></pre>
<script>
async function testAdd() {
  const response = await fetch('https://dentwizard.lgstore.com/lg/API/v1/cart/cart.php?action=add', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      product_id: 91754,
      quantity: 1,
      size: 'XLT',
      color: 'Atlas'
    })
  });
  const data = await response.text();
  document.getElementById('result').textContent = data;
}

async function testGet() {
  const response = await fetch('https://dentwizard.lgstore.com/lg/API/v1/cart/cart.php?action=get');
  const data = await response.text();
  document.getElementById('result').textContent = data;
}
</script>
</body>
</html>
```

## ✅ If cart.php Works:

Update React app to use cart.php:
1. Change API endpoints in cartSlice.js
2. From: `/cart/add.php` to `/cart/cart.php?action=add`
3. From: `/cart/get.php` to `/cart/cart.php?action=get`

## 🎯 Why This Should Work:

- Avoids potentially blocked "add.php" filename
- Single file, simpler to debug
- Same pattern works in many PHP applications
- No complex dependencies

Try this now!