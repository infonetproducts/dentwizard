import React, { useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import {
  Container,
  Grid,
  Paper,
  TextField,
  Button,
  Typography,
  Box,
  Stepper,
  Step,
  StepLabel,
  Alert,
  CircularProgress,
  IconButton,
  FormControl,
  FormLabel,
  RadioGroup,
  FormControlLabel,
  Radio,
  Checkbox,
  Select,
  MenuItem,
  InputLabel,
  List,
  ListItem,
  ListItemText,
  useMediaQuery,
  useTheme
} from '@mui/material';
import { ArrowBack, LocationOn, Payment, CheckCircle } from '@mui/icons-material';
import { useNavigate } from 'react-router-dom';
import { clearCart } from '../store/slices/cartSlice';
import { fetchUserProfile } from '../store/slices/profileSlice';
import taxService from '../services/taxService';
import shippingService from '../services/shippingService';
import budgetService from '../services/budgetService';
import addressService from '../services/addressService';
import api from '../services/api';

const CheckoutPage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  
  // Redux selectors
  const cart = useSelector((state) => state.cart.items || []);
  const user = useSelector((state) => state.auth.user);
  const profileUser = useSelector((state) => state.profile.user);
  const profileBudget = useSelector((state) => state.profile.budget);
  const isAuthenticated = useSelector((state) => state.auth.isAuthenticated);
  
  const [activeStep, setActiveStep] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [budgetBalance, setBudgetBalance] = useState(null);
  
  const steps = ['Shipping', 'Payment', 'Review'];
  
  // Form data - initialize with empty values
  const [shippingInfo, setShippingInfo] = useState(() => {
    // Load saved shipping info from sessionStorage
    const savedShipping = sessionStorage.getItem('checkoutShipping');
    if (savedShipping) {
      try {
        return JSON.parse(savedShipping);
      } catch (e) {
        console.error('Failed to parse saved shipping info');
      }
    }
    // Default values if no saved data
    return {
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      address: '',
      address2: '',
      city: '',
      state: '',
      zipCode: '',
      country: 'US'
    };
  });
  
  const [paymentMethod, setPaymentMethod] = useState('budget');
  const [shippingMethod, setShippingMethod] = useState('standard');
  const [shippingOptions, setShippingOptions] = useState([]);
  const [taxAmount, setTaxAmount] = useState(0);
  const [errors, setErrors] = useState({});
  const [formData, setFormData] = useState({
    departmentCode: ''
  });
  const [userBudget, setUserBudget] = useState({
    available: 0,
    total: 0
  });
  
  // Saved addresses state variables
  const [savedAddresses, setSavedAddresses] = useState([]);
  const [selectedAddressId, setSelectedAddressId] = useState('');
  const [saveAddress, setSaveAddress] = useState(false);
  const [addressNickname, setAddressNickname] = useState('');
  const [loadingAddresses, setLoadingAddresses] = useState(false);
  
  // Calculate order totals with null safety
  const subtotal = cart.reduce((sum, item) => {
    const price = parseFloat(item.price) || 0;
    const quantity = parseInt(item.quantity) || 0;
    return sum + (price * quantity);
  }, 0);
  const shippingCost = shippingOptions.find(opt => opt.method_id === shippingMethod)?.cost || 0;
  const orderSummary = {
    subtotal: subtotal || 0,
    shipping: shippingCost || 0,
    tax: taxAmount || 0,
    total: (subtotal || 0) + (shippingCost || 0) + (taxAmount || 0)
  };
  
  useEffect(() => {
    // Fetch user profile on mount
    dispatch(fetchUserProfile());
    // Load saved addresses
    loadSavedAddresses();
  }, [dispatch]);
  
  // Load shipping when profile loads
  useEffect(() => {
    if (profileUser) {
      console.log('Profile loaded:', profileUser);
      console.log('Profile budget:', profileBudget);
      // Don't call loadBudget() - use profileBudget instead
      loadShippingMethods();
    }
  }, [profileUser, profileBudget]);
  
  // Update userBudget when profileBudget changes
  useEffect(() => {
    if (profileBudget) {
      const availableBudget = profileBudget.budget_balance || profileBudget.balance || 0;
      const totalBudget = profileBudget.budget_limit || profileBudget.limit || 0;
      
      console.log('Setting budget from profile:', { 
        available: availableBudget, 
        total: totalBudget,
        profileBudget 
      });
      
      setUserBudget({
        available: availableBudget,
        total: totalBudget
      });
      setBudgetBalance(availableBudget);
    }
  }, [profileBudget]);
  
  // Update form when user data loads
  useEffect(() => {
    if (profileUser) {
      // Only update fields that are empty to preserve user edits
      const nameParts = (profileUser.name || '').split(' ');
      const firstName = nameParts[0] || '';
      const lastName = nameParts.slice(1).join(' ') || '';
      
      setShippingInfo(prev => ({
        ...prev,
        // Only set if field is empty, preserving user's manual entries
        firstName: prev.firstName || firstName,
        lastName: prev.lastName || lastName,
        email: prev.email || profileUser.email || '',
        phone: prev.phone || profileUser.phone || ''
        // Don't overwrite address fields - user may want different shipping address
      }));
    }
  }, [profileUser]);
  
  // Update user budget when profile budget changes
  useEffect(() => {
    if (profileBudget) {
      setUserBudget({
        available: profileBudget.budget_balance || 0,
        total: profileBudget.budget_limit || 0
      });
      setBudgetBalance(profileBudget.budget_balance || 0);
    }
  }, [profileBudget]);
  
  // Saved address functions
  const loadSavedAddresses = async () => {
    setLoadingAddresses(true);
    try {
      const addresses = await addressService.getSavedAddresses();
      setSavedAddresses(addresses);
      
      // If user has a default address, select it automatically
      const defaultAddress = addresses.find(a => a.is_default);
      if (defaultAddress && !shippingInfo.address) {
        handleSelectSavedAddress(defaultAddress.id);
      }
    } catch (error) {
      console.error('Failed to load addresses:', error);
    } finally {
      setLoadingAddresses(false);
    }
  };
  
  const handleSelectSavedAddress = (addressId) => {
    if (!addressId) {
      // Clear form if "Enter new address" is selected
      setSelectedAddressId('');
      setShippingInfo({
        firstName: profileUser?.name?.split(' ')[0] || '',
        lastName: profileUser?.name?.split(' ').slice(1).join(' ') || '',
        email: profileUser?.email || '',
        phone: profileUser?.phone || '',
        address: '',
        address2: '',
        city: '',
        state: '',
        zipCode: '',
        country: 'US'
      });
      return;
    }
    
    const address = savedAddresses.find(a => a.id === addressId);
    if (address) {
      setShippingInfo({
        firstName: address.first_name || '',
        lastName: address.last_name || '',
        email: shippingInfo.email || profileUser?.email || '',
        phone: address.phone || shippingInfo.phone || profileUser?.phone || '',
        address: address.address1 || '',
        address2: address.address2 || '',
        city: address.city || '',
        state: address.state || '',
        zipCode: address.zip || '',
        country: address.country || 'US'
      });
      setSelectedAddressId(addressId);
    }
  };
  
  const handleSaveNewAddress = async () => {
    if (!saveAddress || !addressNickname.trim()) return null;
    
    try {
      const addressData = {
        nickname: addressNickname,
        first_name: shippingInfo.firstName,
        last_name: shippingInfo.lastName,
        address1: shippingInfo.address,
        address2: shippingInfo.address2,
        city: shippingInfo.city,
        state: shippingInfo.state,
        zip: shippingInfo.zipCode,
        country: shippingInfo.country,
        phone: shippingInfo.phone,
        is_default: savedAddresses.length === 0
      };
      
      const response = await addressService.saveAddress(addressData);
      setSuccess('Address saved to your profile');
      loadSavedAddresses();
      return response;
    } catch (error) {
      console.error('Failed to save address:', error);
      return null;
    }
  };
  
  // Save shipping info to sessionStorage whenever it changes
  useEffect(() => {
    sessionStorage.setItem('checkoutShipping', JSON.stringify(shippingInfo));
  }, [shippingInfo]);
  
  useEffect(() => {
    if (shippingInfo.address && shippingInfo.city && shippingInfo.state && shippingInfo.zipCode) {
      calculateTax();
    }
  }, [shippingInfo, subtotal, shippingCost]);
  
  // Remove loadBudget since we're using profileBudget directly
  // Budget is loaded from profile state, no need for separate loading
  
  const loadShippingMethods = async () => {
    try {
      // Use clientId from profileUser or user
      const clientIdToUse = profileUser?.clientId || user?.clientId || 0;
      const methods = await shippingService.getShippingMethods(clientIdToUse, subtotal);
      
      if (Array.isArray(methods) && methods.length > 0) {
        // Map methods to have consistent structure
        const mappedMethods = methods.map(m => ({
          method_id: m.id || m.method_id,
          method_name: m.name || m.method_name,
          cost: parseFloat(m.cost) || 0
        }));
        
        setShippingOptions(mappedMethods);
        if (mappedMethods.length > 0) {
          setShippingMethod(mappedMethods[0].method_id);
        }
      } else {
        console.warn('Shipping methods response was not an array:', methods);
        setShippingOptions([{
          method_id: 'standard',
          method_name: 'Standard Shipping',
          cost: 10
        }]);
      }
    } catch (error) {
      console.error('Error loading shipping methods:', error);
      setShippingOptions([{
        method_id: 'standard',
        method_name: 'Standard Shipping',
        cost: 10
      }]);
    }
  };
  
  const calculateTax = async () => {
    console.log('Calculating tax with:', {
      state: shippingInfo.state,
      zip: shippingInfo.zipCode,
      city: shippingInfo.city,
      subtotal,
      shippingCost
    });
    
    console.log('Cart items for tax calculation:', cart);
    
    try {
      const taxItems = cart && cart.length > 0 ? cart.map(item => {
        const taxCode = item.tax_code || item.taxCode || '20010';
        console.log(`Item ${item.product_name}: tax_code = ${taxCode}`);
        return {
          id: item.product_id || item.id || '',
          name: item.product_name || item.name || '',
          price: item.price || 0,
          quantity: item.quantity || 0,
          taxCode: taxCode
        };
      }) : [];
      
      const taxData = await taxService.calculateTax(
        {
          state: shippingInfo.state,
          zipCode: shippingInfo.zipCode,
          city: shippingInfo.city,
          address: shippingInfo.address
        },
        taxItems,
        subtotal
      );
      
      console.log('Tax calculation response:', taxData);
      const taxToCollect = taxData?.tax || 0;
      setTaxAmount(taxToCollect);
    } catch (error) {
      console.error('Error calculating tax:', error);
      // Fallback to 8% if tax calculation fails
      setTaxAmount((subtotal + shippingCost) * 0.08);
    }
  };
  
  const validateShipping = () => {
    const newErrors = {};
    
    if (!shippingInfo.firstName) newErrors.firstName = 'First name is required';
    if (!shippingInfo.lastName) newErrors.lastName = 'Last name is required';
    if (!shippingInfo.email) newErrors.email = 'Email is required';
    if (!shippingInfo.address) newErrors.address = 'Address is required';
    if (!shippingInfo.city) newErrors.city = 'City is required';
    if (!shippingInfo.state) newErrors.state = 'State is required';
    if (!shippingInfo.zipCode) newErrors.zipCode = 'ZIP code is required';
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };
  
  const handleShippingChange = (field) => (event) => {
    setShippingInfo({
      ...shippingInfo,
      [field]: event.target.value
    });
    // Clear error for this field
    if (errors[field]) {
      setErrors({
        ...errors,
        [field]: ''
      });
    }
  };
  
  const handleInputChange = (event) => {
    setFormData({
      ...formData,
      [event.target.name]: event.target.value
    });
  };
  
  const handleNext = () => {
    if (activeStep === 0) {
      if (!validateShipping()) return;
    }
    setActiveStep((prevStep) => prevStep + 1);
  };
  
  const handleBack = () => {
    setActiveStep((prevStep) => prevStep - 1);
  };
  
  const handlePlaceOrder = async () => {
    if (!validateShipping()) return;
    
    setLoading(true);
    setError('');
    
    try {
      // Save address if requested
      if (saveAddress && addressNickname.trim()) {
        await handleSaveNewAddress();
      }
      
      // Check budget if using budget payment
      if (paymentMethod === 'budget') {
        const currentBudget = await budgetService.getUserBudget();
        if (!currentBudget || (currentBudget.available || 0) < orderSummary.total) {
          throw new Error('Insufficient budget balance');
        }
      }
      
      // Prepare order data
      const orderData = {
        // Add authentication to body since proxy doesn't forward headers
        auth_token: localStorage.getItem('authToken'),
        user_id: localStorage.getItem('userId'),
        shippingAddress: {
          name: `${shippingInfo.firstName} ${shippingInfo.lastName}`,
          address: shippingInfo.address,
          address2: shippingInfo.address2,
          city: shippingInfo.city,
          state: shippingInfo.state,
          zip: shippingInfo.zipCode,
          phone: shippingInfo.phone
        },
        paymentMethod: paymentMethod,
        departmentCode: formData.departmentCode,
        shippingMethod: shippingMethod,
        shippingCost: shippingCost,
        tax: taxAmount,
        subtotal: subtotal,
        total: orderSummary.total,
        notes: '',
        items: cart && cart.length > 0 ? cart.map(item => ({
          product_id: item.product_id || item.id || '',
          name: item.name || item.product_name || '',
          quantity: item.quantity || 0,
          price: item.price || 0,
          size: item.size || '',
          color: item.color || '',
          logo: item.artwork || item.logo || ''
        })) : []
      };
      
      // Submit order to the correct endpoint
      const response = await api.post('/orders/create.php', orderData);
      
      if (response.data.success) {
        // Store some details for the confirmation page
        localStorage.setItem('lastOrderTotal', orderSummary.total.toFixed(2));
        localStorage.setItem('orderEmail', shippingInfo.email);
        
        setSuccess(true);
        dispatch(clearCart());
        
        // Refresh user profile to update budget balance
        dispatch(fetchUserProfile());
        
        // Redirect to order confirmation with formatted order number
        setTimeout(() => {
          navigate(`/order-confirmation?oid=${response.data.orderNumber || response.data.orderId || response.data.order_id}`);
        }, 1500);
      }
    } catch (err) {
      setError(err.message || 'Failed to place order');
    } finally {
      setLoading(false);
    }
  };
  
  if ((!cart || cart.length === 0) && !success) {
    return (
      <Container maxWidth="lg" sx={{ py: 4 }}>
        <Paper sx={{ p: 4, textAlign: 'center' }}>
          <Typography variant="h5" gutterBottom>
            Your cart is empty
          </Typography>
          <Button
            variant="contained"
            onClick={() => navigate('/products')}
            sx={{ mt: 2 }}
          >
            Continue Shopping
          </Button>
        </Paper>
      </Container>
    );
  }
  
  return (
    <Container maxWidth="lg" sx={{ py: { xs: 2, md: 4 } }}>
      <Box sx={{ mb: 3, display: 'flex', alignItems: 'center', gap: 2 }}>
        <IconButton onClick={() => navigate('/cart')} size="small">
          <ArrowBack />
        </IconButton>
        <Typography variant="h4" component="h1">Checkout</Typography>
      </Box>

      {budgetBalance !== null && budgetBalance >= 0 && (
        <Alert severity="info" sx={{ mb: 2 }}>
          Your available budget: <strong>${(budgetBalance || 0).toFixed(2)}</strong>
        </Alert>
      )}

      {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
      {success && (
        <Alert severity="success" sx={{ mb: 2 }}>
          Order placed successfully! Redirecting to orders...
        </Alert>
      )}

      <Stepper activeStep={activeStep} sx={{ mb: 4 }} alternativeLabel={isMobile}>
        {steps.map((label) => (
          <Step key={label}>
            <StepLabel>{label}</StepLabel>
          </Step>
        ))}
      </Stepper>

      <Grid container spacing={3}>
        <Grid item xs={12} md={8}>
          <Paper sx={{ p: { xs: 2, md: 3 } }}>
            {activeStep === 0 && (
              <Box>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <LocationOn color="primary" />
                  Shipping Information
                </Typography>
                
                {/* Saved Addresses Dropdown */}
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
                
                <Grid container spacing={2} sx={{ mt: 1 }}>
                  <Grid item xs={12} sm={6}>
                    <TextField
                      fullWidth
                      label="First Name"
                      value={shippingInfo.firstName}
                      onChange={handleShippingChange('firstName')}
                      error={!!errors.firstName}
                      helperText={errors.firstName}
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField
                      fullWidth
                      label="Last Name"
                      value={shippingInfo.lastName}
                      onChange={handleShippingChange('lastName')}
                      error={!!errors.lastName}
                      helperText={errors.lastName}
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField
                      fullWidth
                      label="Email"
                      type="email"
                      value={shippingInfo.email}
                      onChange={handleShippingChange('email')}
                      error={!!errors.email}
                      helperText={errors.email}
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField
                      fullWidth
                      label="Phone"
                      value={shippingInfo.phone}
                      onChange={handleShippingChange('phone')}
                      error={!!errors.phone}
                      helperText={errors.phone}
                    />
                  </Grid>
                  <Grid item xs={12}>
                    <TextField
                      fullWidth
                      label="Address Line 1"
                      value={shippingInfo.address}
                      onChange={handleShippingChange('address')}
                      error={!!errors.address}
                      helperText={errors.address}
                    />
                  </Grid>                  <Grid item xs={12}>
                    <TextField
                      fullWidth
                      label="Address Line 2 (Optional)"
                      value={shippingInfo.address2}
                      onChange={handleShippingChange('address2')}
                    />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField
                      fullWidth
                      label="City"
                      value={shippingInfo.city}
                      onChange={handleShippingChange('city')}
                      error={!!errors.city}
                      helperText={errors.city}
                    />
                  </Grid>
                  <Grid item xs={12} sm={4}>
                    <TextField
                      fullWidth
                      label="State"
                      value={shippingInfo.state}
                      onChange={handleShippingChange('state')}
                      error={!!errors.state}
                      helperText={errors.state}
                    />
                  </Grid>
                  <Grid item xs={12} sm={2}>
                    <TextField
                      fullWidth                      label="ZIP Code"
                      value={shippingInfo.zipCode}
                      onChange={handleShippingChange('zipCode')}
                      error={!!errors.zipCode}
                      helperText={errors.zipCode}
                    />
                  </Grid>
                  
                  {/* Save Address Checkbox - Only show if not using a saved address */}
                  {selectedAddressId === "" && (
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
                            label="Address Nickname (e.g., Home, Office, Warehouse)"
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
                  )}
                  
                </Grid>
                <Box sx={{ mt: 3, display: 'flex', justifyContent: 'flex-end' }}>
                  <Button
                    variant="contained"
                    onClick={handleNext}
                    disabled={loading}
                  >
                    Continue to Payment
                  </Button>
                </Box>
              </Box>
            )}

            {activeStep === 1 && (
              <Box>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Payment color="primary" />
                  Payment Method
                </Typography>
                
                <FormControl fullWidth sx={{ mt: 2 }}>
                  <FormLabel>Shipping Method</FormLabel>
                  <RadioGroup                    value={shippingMethod}
                    onChange={(e) => setShippingMethod(e.target.value)}
                  >
                    {shippingOptions && shippingOptions.length > 0 ? shippingOptions.map((option) => (
                      <FormControlLabel
                        key={option.method_id}
                        value={option.method_id}
                        control={<Radio />}
                        label={`${option.method_name || 'Shipping'} - $${(option.cost || 0).toFixed(2)}`}
                      />
                    )) : (
                      <FormControlLabel
                        value="standard"
                        control={<Radio />}
                        label="Standard Shipping - $10.00"
                      />
                    )}
                  </RadioGroup>
                </FormControl>

                <FormControl fullWidth sx={{ mt: 3 }}>
                  <FormLabel>Payment Method</FormLabel>
                  <RadioGroup
                    value={paymentMethod}
                    onChange={(e) => setPaymentMethod(e.target.value)}
                  >
                    <FormControlLabel
                      value="budget"
                      control={<Radio />}
                      label={`Use Budget Balance ($${(userBudget.available || 0).toFixed(2)} available)`}
                      disabled={!userBudget.available || userBudget.available <= 0}
                    />
                    {[85, 86, 89].includes(parseInt(profileUser?.clientId || user?.clientId || 0)) && (
                      <FormControlLabel
                        value="department"
                        control={<Radio />}
                        label="Department/Billing Code"
                      />
                    )}
                  </RadioGroup>
                </FormControl>

                {paymentMethod === 'department' && (
                  <TextField
                    fullWidth
                    label="Department/Billing Code"
                    name="departmentCode"
                    value={formData.departmentCode}
                    onChange={handleInputChange}
                    required
                    sx={{ mt: 2 }}
                  />
                )}

                <Box sx={{ mt: 3, display: 'flex', justifyContent: 'space-between' }}>
                  <Button onClick={handleBack}>
                    Back
                  </Button>
                  <Button
                    variant="contained"                    onClick={handleNext}
                    disabled={loading}
                  >
                    Review Order
                  </Button>
                </Box>
              </Box>
            )}

            {activeStep === 2 && (
              <Box>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <CheckCircle color="primary" />
                  Review Your Order
                </Typography>
                
                <Box sx={{ mt: 2 }}>
                  <Typography variant="subtitle1" gutterBottom>
                    Shipping Information
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    {shippingInfo.firstName} {shippingInfo.lastName}<br />
                    {shippingInfo.address}<br />
                    {shippingInfo.address2 && <>{shippingInfo.address2}<br /></>}
                    {shippingInfo.city}, {shippingInfo.state} {shippingInfo.zipCode}<br />
                    {shippingInfo.email}<br />
                    {shippingInfo.phone}
                  </Typography>
                </Box>
                <Box sx={{ mt: 3 }}>
                  <Typography variant="subtitle1" gutterBottom>
                    Payment Method
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    {paymentMethod === 'budget' && 'Budget Balance'}
                    {paymentMethod === 'card' && 'Credit Card'}
                    {paymentMethod === 'department' && `Department/Billing Code: ${formData.departmentCode}`}
                  </Typography>
                </Box>

                <Box sx={{ mt: 3, display: 'flex', justifyContent: 'space-between' }}>
                  <Button onClick={handleBack}>
                    Back
                  </Button>
                  <Button
                    variant="contained"
                    color="primary"
                    onClick={handlePlaceOrder}
                    disabled={loading}
                  >
                    {loading ? <CircularProgress size={24} /> : 'Place Order'}
                  </Button>
                </Box>
              </Box>
            )}
          </Paper>
        </Grid>

        <Grid item xs={12} md={4}>          <Paper sx={{ p: { xs: 2, md: 3 } }}>
            <Typography variant="h6" gutterBottom>
              Order Summary
            </Typography>
            <List>
              {cart && cart.length > 0 ? cart.map((item) => (
                <ListItem key={item.product_id || item.id} divider>
                  <ListItemText
                    primary={item.product_name || item.name || 'Unknown Product'}
                    secondary={
                      <>
                        Qty: {item.quantity || 0}
                        {item.size && ` | Size: ${item.size}`}
                        {item.color && ` | Color: ${item.color}`}
                        {item.artwork && ` | Logo: ${item.artwork}`}
                        {item.logo && ` | Logo: ${item.logo}`}
                      </>
                    }
                  />
                  <Typography>
                    ${((item.price || 0) * (item.quantity || 0)).toFixed(2)}
                  </Typography>
                </ListItem>
              )) : (
                <ListItem>
                  <ListItemText primary="No items in cart" />
                </ListItem>
              )}
              <ListItem>
                <ListItemText primary="Subtotal" />
                <Typography>${(orderSummary.subtotal || 0).toFixed(2)}</Typography>
              </ListItem>
              <ListItem>
                <ListItemText primary="Shipping" />
                <Typography>${(orderSummary.shipping || 0).toFixed(2)}</Typography>
              </ListItem>
              <ListItem>
                <ListItemText primary="Tax" />
                <Typography>${(orderSummary.tax || 0).toFixed(2)}</Typography>
              </ListItem>
              <ListItem>
                <ListItemText primary={<strong>Total</strong>} />
                <Typography variant="h6">${(orderSummary.total || 0).toFixed(2)}</Typography>
              </ListItem>
            </List>
          </Paper>
        </Grid>
      </Grid>
    </Container>
  );
};

export default CheckoutPage;