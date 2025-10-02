## Saved Addresses Integration - Implementation Guide

Since your PHP backend already handles saved addresses, here's what you need to do:

### 1. Your Existing PHP System Has:
- Primary shipping address in user profile
- Additional shipping addresses (add_shipping_address.php)
- Database tables already exist

### 2. Required PHP Endpoints
You need to create or verify these endpoints exist at `/lg/API/v1/`:

```php
// GET /user/addresses - Returns all addresses for the user
// POST /user/addresses - Add new address  
// PUT /user/addresses/{id} - Update address
// DELETE /user/addresses/{id} - Delete address
```

### 3. Quick PHP Endpoint for Testing
Create this simple endpoint at `/lg/API/v1/user/addresses.php`:

```php
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// For testing - returns mock data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // In production, fetch from your existing database
    echo json_encode([
        'status' => 'success',
        'data' => [
            [
                'id' => 1,
                'nickname' => 'Office',
                'address1' => '123 Main St',
                'city' => 'Erie',
                'state' => 'PA',
                'zip' => '16501'
            ]
        ]
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save to your existing address table
    echo json_encode(['status' => 'success']);
}
?>
```

### 4. CheckoutPage.js Updates Needed

The CheckoutPage.js already has most of what you need:
- ✅ State variables for saved addresses
- ✅ Functions to load and select addresses
- ✅ addressService imported

You just need to add the UI components in the render section.

### 5. Add UI Components to CheckoutPage.js

Find the "Shipping Information" section (around line 500-600) and add:

```jsx
{/* After the "Shipping Information" Typography */}

{/* Saved Addresses Dropdown */}
{savedAddresses.length > 0 && (
  <Box sx={{ mb: 2 }}>
    <FormControl fullWidth>
      <Select
        value={selectedAddressId}
        onChange={(e) => handleSelectSavedAddress(e.target.value)}
        displayEmpty
        fullWidth
      >
        <MenuItem value="">
          <em>Enter new address</em>
        </MenuItem>
        {savedAddresses.map((address) => (
          <MenuItem key={address.id} value={address.id}>
            {address.nickname} - {address.address1}, {address.city}
          </MenuItem>
        ))}
      </Select>
    </FormControl>
  </Box>
)}

{/* After all the address input fields, add: */}

{/* Save Address Checkbox */}
<Box sx={{ mt: 2 }}>
  <FormControlLabel
    control={
      <Checkbox
        checked={saveAddress}
        onChange={(e) => setSaveAddress(e.target.checked)}
      />
    }
    label="Save this address for future orders"
  />
  {saveAddress && (
    <TextField
      fullWidth
      label="Address Nickname (e.g., Home, Office)"
      value={addressNickname}
      onChange={(e) => setAddressNickname(e.target.value)}
      sx={{ mt: 1 }}
    />
  )}
</Box>
```

### 6. Testing the Integration

1. Run your React app
2. Go to checkout page
3. Open browser console
4. Run the test file commands:
   ```javascript
   // Test if addresses load
   fetch('http://localhost/lg/API/v1/user/addresses')
     .then(r => r.json())
     .then(data => console.log('Addresses:', data));
   ```

### 7. Database Connection
Your existing PHP uses these credentials:
- Host: localhost
- User: rwaf
- Database: rwaf

The addresses likely use your existing address fields:
- Address1, Address2
- City, State, Zip
- ShipToName, ShipToDept

### Next Steps:

1. **Deploy the addresses.php endpoint** (or verify it exists)
2. **Add the UI components** to CheckoutPage.js where indicated
3. **Test the integration** using the test file
4. **Verify addresses save** when placing an order

The system will then:
- Load saved addresses when checkout opens
- Let users select from dropdown
- Save new addresses with nicknames
- Work with your existing PHP profile page

Since you already have the PHP backend infrastructure, this should integrate smoothly!
