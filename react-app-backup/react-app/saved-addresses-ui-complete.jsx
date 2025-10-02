// Complete Saved Addresses Integration for CheckoutPage.js
// Add these UI components in your render/return section

// 1. FIND THE SHIPPING INFORMATION SECTION AND ADD THIS RIGHT AFTER THE TITLE:

{/* Saved Addresses Selection */}
{savedAddresses.length > 0 && (
  <Box sx={{ mb: 3 }}>
    <FormControl fullWidth>
      <Typography variant="subtitle2" gutterBottom sx={{ mb: 1 }}>
        Use a saved address:
      </Typography>
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
            <Box>
              <Typography variant="body2">
                {address.nickname || 'Saved Address'}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                {address.address1}, {address.city}, {address.state} {address.zip}
              </Typography>
            </Box>
          </MenuItem>
        ))}
      </Select>
    </FormControl>
    
    {loadingAddresses && (
      <Box sx={{ mt: 1 }}>
        <CircularProgress size={20} />
        <Typography variant="caption" sx={{ ml: 1 }}>
          Loading addresses...
        </Typography>
      </Box>
    )}
  </Box>
)}

{/* Optional: Show a divider if using saved addresses */}
{savedAddresses.length > 0 && (
  <Divider sx={{ my: 2 }}>
    <Typography variant="caption" color="text.secondary">
      OR ENTER NEW ADDRESS
    </Typography>
  </Divider>
)}

// 2. ADD THIS AT THE BOTTOM OF YOUR SHIPPING ADDRESS FORM (after all the address fields):

{/* Save Address Checkbox */}
<Box sx={{ mt: 3, mb: 2, p: 2, bgcolor: 'grey.50', borderRadius: 1 }}>
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
      label="Address Nickname"
      value={addressNickname}
      onChange={(e) => setAddressNickname(e.target.value)}
      placeholder="e.g., Home, Office, Warehouse"
      helperText="Give this address a memorable name"
      sx={{ mt: 2 }}
      required
      error={saveAddress && !addressNickname.trim()}
    />
  )}
</Box>

// 3. IF YOU HAVE A QUICK FILL BUTTON, ADD THIS:

{profileUser && !selectedAddressId && (
  <Button
    variant="outlined"
    size="small"
    onClick={() => {
      // Auto-fill from profile
      setShippingInfo(prev => ({
        ...prev,
        firstName: profileUser.name?.split(' ')[0] || '',
        lastName: profileUser.name?.split(' ').slice(1).join(' ') || '',
        email: profileUser.email || '',
        phone: profileUser.phone || ''
      }));
    }}
    sx={{ mb: 2 }}
  >
    Use Profile Information
  </Button>
)}

// 4. ADD SUCCESS MESSAGE FOR SAVED ADDRESS (in your alerts section):

{success && success.includes('Address saved') && (
  <Alert severity="success" sx={{ mb: 2 }}>
    {success}
    <Button
      size="small"
      onClick={() => navigate('/profile?tab=addresses')}
      sx={{ ml: 2 }}
    >
      View Saved Addresses
    </Button>
  </Alert>
)}