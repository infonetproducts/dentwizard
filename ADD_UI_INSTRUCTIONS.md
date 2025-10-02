## Add Saved Addresses UI to CheckoutPage.js

### Step 1: Find Line 520 (After "Shipping Information" title)
Look for this code around line 520:
```jsx
<Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
  <LocationOn color="primary" />
  Shipping Information
</Typography>
```

### Step 2: Add Saved Addresses Dropdown
Add this code RIGHT AFTER the Typography above and BEFORE the Grid container:

```jsx
{/* Saved Addresses Dropdown - ADD THIS */}
{savedAddresses.length > 0 && (
  <Box sx={{ mb: 3, mt: 2 }}>
    <FormControl fullWidth>
      <InputLabel>Select a saved address</InputLabel>
      <Select
        value={selectedAddressId}
        onChange={(e) => handleSelectSavedAddress(e.target.value)}
        label="Select a saved address"
      >
        <MenuItem value="">
          <em>Enter new address</em>
        </MenuItem>
        {savedAddresses.map((address) => (
          <MenuItem key={address.id} value={address.id}>
            {address.nickname || 'Saved Address'} - {address.address1}, {address.city}, {address.state}
          </MenuItem>
        ))}
      </Select>
    </FormControl>
  </Box>
)}
{/* END Saved Addresses Dropdown */}
```

### Step 3: Find the end of the address form (around line 600)
Look for the country field, it's the last address field in the Grid.

### Step 4: Add Save Address Checkbox
After the last Grid item (country field) but BEFORE the closing Grid container tag, add:

```jsx
{/* Save Address Checkbox - ADD THIS */}
<Grid item xs={12}>
  <Box sx={{ mt: 2, p: 2, bgcolor: 'grey.50', borderRadius: 1 }}>
    <FormControlLabel
      control={
        <Checkbox
          checked={saveAddress}
          onChange={(e) => setSaveAddress(e.target.checked)}
          color="primary"
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
        placeholder="Give this address a memorable name"
        sx={{ mt: 2 }}
        required={saveAddress}
        error={saveAddress && !addressNickname.trim()}
        helperText={saveAddress && !addressNickname.trim() ? "Nickname is required to save address" : ""}
      />
    )}
  </Box>
</Grid>
{/* END Save Address Checkbox */}
```

### Step 5: Add Missing Imports
Make sure these are imported at the top (around line 20):
```jsx
import { 
  // ... existing imports ...
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Checkbox
} from '@mui/material';
```

### Step 6: Test It!
1. Save CheckoutPage.js
2. Run `npm start` if not already running
3. Go to checkout page
4. Open browser console (F12)
5. You should see addresses loading in the Network tab

### Quick Console Test:
```javascript
// Run this in console to verify addresses are loading
console.log('Saved addresses:', savedAddresses);
```

If savedAddresses is undefined, check that the loadSavedAddresses() function is being called in useEffect.
