import React, { useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  Container,
  Grid,
  Paper,
  Typography,
  TextField,
  Button,
  Stepper,
  Step,
  StepLabel,
  FormControlLabel,
  Checkbox,
  Radio,
  RadioGroup,
  Divider,
  List,
  ListItem,
  ListItemText,
  Alert,
  CircularProgress,
  useTheme,
  useMediaQuery,
  Card,
  CardContent,
  IconButton,
  Collapse,
  FormControl,
  FormLabel,
  InputAdornment
} from '@mui/material';
import {
  ShoppingCart,
  LocalShipping,
  Payment,
  CheckCircle,
  ArrowBack,
  Edit,
  ExpandMore,
  ExpandLess,
  Lock,
  CreditCard,
  LocationOn,
  Person
} from '@mui/icons-material';
import { clearCart } from '../store/slices/cartSlice';
import api from '../services/api';

const steps = ['Shipping Info', 'Payment Method', 'Review Order'];

function CheckoutPage() {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const navigate = useNavigate();
  const dispatch = useDispatch();
  
  const { items = [], summary = {} } = useSelector(state => state.cart);
  const total = summary.total || 0;
  const user = useSelector(state => state.auth.user);
  
  const [activeStep, setActiveStep] = useState(0);
  const [loading, setLoading] = useState(false);
  const [orderComplete, setOrderComplete] = useState(false);
  const [orderId, setOrderId] = useState(null);
  const [errors, setErrors] = useState({});
  const [showOrderSummary, setShowOrderSummary] = useState(!isMobile);
  
  // Form states
  const [shippingInfo, setShippingInfo] = useState({
    firstName: user?.firstName || '',
    lastName: user?.lastName || '',
    email: user?.email || '',
    phone: '',
    address: '',
    address2: '',
    city: '',
    state: '',
    zipCode: '',
    country: 'USA',
    saveAddress: true,
    useAsBilling: true
  });
  
  const [billingInfo, setBillingInfo] = useState({ ...shippingInfo });
  
  const [paymentInfo, setPaymentInfo] = useState({
    method: 'credit_card',
    cardNumber: '',
    cardName: '',
    expiryDate: '',
    cvv: '',
    saveCard: false
  });

  useEffect(() => {
    if (items.length === 0 && !orderComplete) {
      navigate('/cart');
    }
  }, [items, navigate, orderComplete]);

  const handleShippingChange = (field) => (event) => {
    const value = event.target.type === 'checkbox' ? event.target.checked : event.target.value;
    setShippingInfo({ ...shippingInfo, [field]: value });
    
    if (field === 'useAsBilling' && value) {
      setBillingInfo({ ...shippingInfo, [field]: value });
    }
  };

  const handleBillingChange = (field) => (event) => {
    setBillingInfo({ ...billingInfo, [field]: event.target.value });
  };

  const handlePaymentChange = (field) => (event) => {
    const value = event.target.type === 'checkbox' ? event.target.checked : event.target.value;
    setPaymentInfo({ ...paymentInfo, [field]: value });
  };

  const validateShipping = () => {
    const newErrors = {};
    const required = ['firstName', 'lastName', 'email', 'phone', 'address', 'city', 'state', 'zipCode'];
    
    required.forEach(field => {
      if (!shippingInfo[field]) {
        newErrors[field] = 'This field is required';
      }
    });
    
    // Email validation
    if (shippingInfo.email && !/\S+@\S+\.\S+/.test(shippingInfo.email)) {
      newErrors.email = 'Invalid email address';
    }
    
    // Phone validation
    if (shippingInfo.phone && !/^\d{10}$/.test(shippingInfo.phone.replace(/\D/g, ''))) {
      newErrors.phone = 'Invalid phone number';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const validatePayment = () => {
    const newErrors = {};
    
    if (paymentInfo.method === 'credit_card') {
      if (!paymentInfo.cardNumber) newErrors.cardNumber = 'Card number is required';
      if (!paymentInfo.cardName) newErrors.cardName = 'Cardholder name is required';
      if (!paymentInfo.expiryDate) newErrors.expiryDate = 'Expiry date is required';
      if (!paymentInfo.cvv) newErrors.cvv = 'CVV is required';
      
      // Basic card number validation
      const cardNum = paymentInfo.cardNumber.replace(/\s/g, '');
      if (cardNum.length < 15 || cardNum.length > 16) {
        newErrors.cardNumber = 'Invalid card number';
      }
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleNext = () => {
    if (activeStep === 0) {
      if (validateShipping()) {
        setActiveStep(1);
      }
    } else if (activeStep === 1) {
      if (validatePayment()) {
        setActiveStep(2);
      }
    } else {
      handlePlaceOrder();
    }
  };

  const handleBack = () => {
    setActiveStep(activeStep - 1);
  };

  const handlePlaceOrder = async () => {
    setLoading(true);
    try {
      const orderData = {
        items: items.map(item => ({
          product_id: item.id,
          quantity: item.quantity,
          size: item.size,
          color: item.color,
          price: item.price
        })),
        shipping: shippingInfo,
        billing: shippingInfo.useAsBilling ? shippingInfo : billingInfo,
        payment: {
          method: paymentInfo.method,
          // Don't send sensitive card data to the server in production
          last4: paymentInfo.cardNumber.slice(-4)
        },
        total: total || 0
      };

      const response = await api.post('/orders', orderData);
      
      setOrderId(response.data.order_id);
      setOrderComplete(true);
      dispatch(clearCart());
      
      // Scroll to top
      window.scrollTo(0, 0);
    } catch (error) {
      console.error('Order failed:', error);
      setErrors({ submit: 'Failed to place order. Please try again.' });
    } finally {
      setLoading(false);
    }
  };

  const formatCardNumber = (value) => {
    const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    const matches = v.match(/\d{4,16}/g);
    const match = (matches && matches[0]) || '';
    const parts = [];
    for (let i = 0, len = match.length; i < len; i += 4) {
      parts.push(match.substring(i, i + 4));
    }
    if (parts.length) {
      return parts.join(' ');
    } else {
      return value;
    }
  };

  if (orderComplete) {
    return (
      <Container maxWidth="md" sx={{ py: 4 }}>
        <Paper sx={{ p: 4, textAlign: 'center' }}>
          <CheckCircle color="success" sx={{ fontSize: 64, mb: 2 }} />
          <Typography variant="h4" gutterBottom>
            Order Placed Successfully!
          </Typography>
          <Typography variant="body1" color="text.secondary" paragraph>
            Thank you for your order. Your order number is <strong>#{orderId}</strong>
          </Typography>
          <Typography variant="body2" color="text.secondary" paragraph>
            You will receive an email confirmation shortly with your order details.
          </Typography>
          <Box sx={{ mt: 4, display: 'flex', gap: 2, justifyContent: 'center', flexWrap: 'wrap' }}>
            <Button
              variant="contained"
              size="large"
              onClick={() => navigate('/orders')}
              startIcon={<ShoppingCart />}
            >
              View Orders
            </Button>
            <Button
              variant="outlined"
              size="large"
              onClick={() => navigate('/products')}
            >
              Continue Shopping
            </Button>
          </Box>
        </Paper>
      </Container>
    );
  }

  return (
    <Container maxWidth="lg" sx={{ py: { xs: 2, md: 4 } }}>
      {/* Header */}
      <Box sx={{ mb: 3, display: 'flex', alignItems: 'center', gap: 2 }}>
        <IconButton onClick={() => navigate('/cart')} size="small">
          <ArrowBack />
        </IconButton>
        <Typography variant="h4" component="h1">
          Checkout
        </Typography>
      </Box>

      {/* Stepper */}
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
            {/* Step Content */}
            {activeStep === 0 && (
              <Box>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <LocationOn color="primary" />
                  Shipping Information
                </Typography>
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
                  </Grid>
                  <Grid item xs={12}>
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
                  <Grid item xs={12} sm={3}>
                    <TextField
                      fullWidth
                      label="State"
                      value={shippingInfo.state}
                      onChange={handleShippingChange('state')}
                      error={!!errors.state}
                      helperText={errors.state}
                    />
                  </Grid>
                  <Grid item xs={12} sm={3}>
                    <TextField
                      fullWidth
                      label="ZIP Code"
                      value={shippingInfo.zipCode}
                      onChange={handleShippingChange('zipCode')}
                      error={!!errors.zipCode}
                      helperText={errors.zipCode}
                    />
                  </Grid>
                  <Grid item xs={12}>
                    <FormControlLabel
                      control={
                        <Checkbox
                          checked={shippingInfo.saveAddress}
                          onChange={handleShippingChange('saveAddress')}
                        />
                      }
                      label="Save this address for future orders"
                    />
                  </Grid>
                  <Grid item xs={12}>
                    <FormControlLabel
                      control={
                        <Checkbox
                          checked={shippingInfo.useAsBilling}
                          onChange={handleShippingChange('useAsBilling')}
                        />
                      }
                      label="Use as billing address"
                    />
                  </Grid>
                </Grid>
              </Box>
            )}

            {activeStep === 1 && (
              <Box>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Payment color="primary" />
                  Payment Method
                </Typography>
                
                <FormControl component="fieldset" sx={{ mt: 2 }}>
                  <RadioGroup
                    value={paymentInfo.method}
                    onChange={handlePaymentChange('method')}
                  >
                    <FormControlLabel
                      value="credit_card"
                      control={<Radio />}
                      label={
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                          <CreditCard />
                          Credit/Debit Card
                        </Box>
                      }
                    />
                    <FormControlLabel
                      value="purchase_order"
                      control={<Radio />}
                      label="Purchase Order"
                    />
                  </RadioGroup>
                </FormControl>

                {paymentInfo.method === 'credit_card' && (
                  <Grid container spacing={2} sx={{ mt: 2 }}>
                    <Grid item xs={12}>
                      <TextField
                        fullWidth
                        label="Card Number"
                        value={formatCardNumber(paymentInfo.cardNumber)}
                        onChange={(e) => {
                          const value = e.target.value.replace(/\s/g, '');
                          if (value.length <= 16) {
                            handlePaymentChange('cardNumber')({ target: { value } });
                          }
                        }}
                        error={!!errors.cardNumber}
                        helperText={errors.cardNumber}
                        InputProps={{
                          startAdornment: (
                            <InputAdornment position="start">
                              <CreditCard />
                            </InputAdornment>
                          ),
                        }}
                      />
                    </Grid>
                    <Grid item xs={12}>
                      <TextField
                        fullWidth
                        label="Cardholder Name"
                        value={paymentInfo.cardName}
                        onChange={handlePaymentChange('cardName')}
                        error={!!errors.cardName}
                        helperText={errors.cardName}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <TextField
                        fullWidth
                        label="Expiry Date (MM/YY)"
                        value={paymentInfo.expiryDate}
                        onChange={handlePaymentChange('expiryDate')}
                        placeholder="MM/YY"
                        error={!!errors.expiryDate}
                        helperText={errors.expiryDate}
                      />
                    </Grid>
                    <Grid item xs={6}>
                      <TextField
                        fullWidth
                        label="CVV"
                        value={paymentInfo.cvv}
                        onChange={handlePaymentChange('cvv')}
                        error={!!errors.cvv}
                        helperText={errors.cvv}
                        InputProps={{
                          startAdornment: (
                            <InputAdornment position="start">
                              <Lock />
                            </InputAdornment>
                          ),
                        }}
                      />
                    </Grid>
                    <Grid item xs={12}>
                      <FormControlLabel
                        control={
                          <Checkbox
                            checked={paymentInfo.saveCard}
                            onChange={handlePaymentChange('saveCard')}
                          />
                        }
                        label="Save card for future purchases"
                      />
                    </Grid>
                  </Grid>
                )}
              </Box>
            )}

            {activeStep === 2 && (
              <Box>
                <Typography variant="h6" gutterBottom>
                  Review Your Order
                </Typography>
                
                {/* Shipping Summary */}
                <Card sx={{ mb: 2 }}>
                  <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                      <Typography variant="subtitle1" fontWeight="bold">
                        Shipping Address
                      </Typography>
                      <IconButton size="small" onClick={() => setActiveStep(0)}>
                        <Edit />
                      </IconButton>
                    </Box>
                    <Typography variant="body2">
                      {shippingInfo.firstName} {shippingInfo.lastName}<br />
                      {shippingInfo.address}<br />
                      {shippingInfo.address2 && <>{shippingInfo.address2}<br /></>}
                      {shippingInfo.city}, {shippingInfo.state} {shippingInfo.zipCode}<br />
                      {shippingInfo.phone}
                    </Typography>
                  </CardContent>
                </Card>

                {/* Payment Summary */}
                <Card sx={{ mb: 2 }}>
                  <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1 }}>
                      <Typography variant="subtitle1" fontWeight="bold">
                        Payment Method
                      </Typography>
                      <IconButton size="small" onClick={() => setActiveStep(1)}>
                        <Edit />
                      </IconButton>
                    </Box>
                    <Typography variant="body2">
                      {paymentInfo.method === 'credit_card' ? (
                        <>
                          Credit Card ending in {paymentInfo.cardNumber.slice(-4)}<br />
                          {paymentInfo.cardName}
                        </>
                      ) : (
                        'Purchase Order'
                      )}
                    </Typography>
                  </CardContent>
                </Card>

                {/* Order Items */}
                <Card>
                  <CardContent>
                    <Typography variant="subtitle1" fontWeight="bold" gutterBottom>
                      Order Items
                    </Typography>
                    <List disablePadding>
                      {items.map((item) => (
                        <ListItem key={`${item.id}-${item.size}-${item.color}`} sx={{ px: 0 }}>
                          <ListItemText
                            primary={item.name}
                            secondary={`Size: ${item.size}, Color: ${item.color}, Qty: ${item.quantity}`}
                          />
                          <Typography variant="body2">
                            ${(item.price * item.quantity).toFixed(2)}
                          </Typography>
                        </ListItem>
                      ))}
                    </List>
                  </CardContent>
                </Card>
              </Box>
            )}

            {/* Error Alert */}
            {errors.submit && (
              <Alert severity="error" sx={{ mt: 2 }}>
                {errors.submit}
              </Alert>
            )}

            {/* Action Buttons */}
            <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 3 }}>
              <Button
                disabled={activeStep === 0}
                onClick={handleBack}
                startIcon={<ArrowBack />}
              >
                Back
              </Button>
              <Button
                variant="contained"
                onClick={handleNext}
                disabled={loading}
                endIcon={loading && <CircularProgress size={20} />}
              >
                {activeStep === steps.length - 1 ? 'Place Order' : 'Next'}
              </Button>
            </Box>
          </Paper>
        </Grid>

        {/* Order Summary Sidebar */}
        <Grid item xs={12} md={4}>
          <Paper sx={{ p: 2, position: { md: 'sticky' }, top: { md: 100 } }}>
            <Box
              sx={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                mb: 2
              }}
            >
              <Typography variant="h6">Order Summary</Typography>
              {isMobile && (
                <IconButton
                  size="small"
                  onClick={() => setShowOrderSummary(!showOrderSummary)}
                >
                  {showOrderSummary ? <ExpandLess /> : <ExpandMore />}
                </IconButton>
              )}
            </Box>
            
            <Collapse in={showOrderSummary || !isMobile}>
              <List disablePadding>
                {items.map((item) => (
                  <ListItem key={`${item.id}-${item.size}-${item.color}`} sx={{ px: 0, py: 1 }}>
                    <ListItemText
                      primary={item.name}
                      secondary={`Qty: ${item.quantity}`}
                      primaryTypographyProps={{ variant: 'body2' }}
                      secondaryTypographyProps={{ variant: 'caption' }}
                    />
                    <Typography variant="body2">
                      ${(item.price * item.quantity).toFixed(2)}
                    </Typography>
                  </ListItem>
                ))}
              </List>
              
              <Divider sx={{ my: 2 }} />
              
              <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                <Typography variant="body2">Subtotal</Typography>
                <Typography variant="body2">${(total || 0).toFixed(2)}</Typography>
              </Box>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                <Typography variant="body2">Shipping</Typography>
                <Typography variant="body2">Free</Typography>
              </Box>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                <Typography variant="body2">Tax</Typography>
                <Typography variant="body2">${((total || 0) * 0.08).toFixed(2)}</Typography>
              </Box>
              
              <Divider sx={{ my: 2 }} />
              
              <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                <Typography variant="h6">Total</Typography>
                <Typography variant="h6" color="primary">
                  ${((total || 0) * 1.08).toFixed(2)}
                </Typography>
              </Box>
            </Collapse>
            
            <Box sx={{ mt: 3, p: 2, bgcolor: 'grey.100', borderRadius: 1 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 1 }}>
                <Lock fontSize="small" color="action" />
                <Typography variant="caption" color="text.secondary">
                  Secure Checkout
                </Typography>
              </Box>
              <Typography variant="caption" color="text.secondary">
                Your payment information is encrypted and secure. We never store your credit card details.
              </Typography>
            </Box>
          </Paper>
        </Grid>
      </Grid>
    </Container>
  );
}

export default CheckoutPage;