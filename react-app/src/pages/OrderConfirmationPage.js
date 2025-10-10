import React, { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import {
  Container,
  Paper,
  Typography,
  Box,
  Button,
  Divider,
  CircularProgress,
  Alert,
  Grid,
  Card,
  CardContent,
  List,
  ListItem,
  ListItemText,
  IconButton
} from '@mui/material';
import {
  CheckCircle,
  Print,
  Home,
  ShoppingBag,
  ContentCopy
} from '@mui/icons-material';

const OrderConfirmationPage = () => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const [orderDetails, setOrderDetails] = useState(null);
  const [loading, setLoading] = useState(true);
  const [copied, setCopied] = useState(false);
  
  const orderId = searchParams.get('oid') || searchParams.get('orderId');
  
  useEffect(() => {
    // Simulate loading order details
    // In production, you might want to fetch order details from API
    setTimeout(() => {
      setOrderDetails({
        orderId: orderId || 'ORD' + Date.now(),
        total: localStorage.getItem('lastOrderTotal') || '0.00',
        email: localStorage.getItem('orderEmail') || 'customer@email.com'
      });
      setLoading(false);
    }, 1000);
  }, [orderId]);
  
  const handleCopyOrderId = () => {
    if (orderDetails?.orderId) {
      navigator.clipboard.writeText(orderDetails.orderId);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };
  
  const handlePrint = () => {
    window.print();
  };
  
  if (loading) {
    return (
      <Container maxWidth="md" sx={{ py: 8 }}>
        <Paper sx={{ p: 4, textAlign: 'center' }}>
          <CircularProgress />
          <Typography sx={{ mt: 2 }}>Loading order details...</Typography>
        </Paper>
      </Container>
    );
  }
  
  return (
    <Container maxWidth="md" sx={{ py: { xs: 4, md: 8 } }}>
      <Paper sx={{ p: { xs: 3, md: 4 } }}>
        {/* Success Header */}
        <Box sx={{ textAlign: 'center', mb: 4 }}>
          <CheckCircle 
            color="success" 
            sx={{ fontSize: 80, mb: 2 }}
          />
          <Typography variant="h4" gutterBottom>
            Order Confirmed!
          </Typography>
          <Typography variant="subtitle1" color="text.secondary">
            Thank you for your order. We've sent a confirmation email to {orderDetails?.email}
          </Typography>
        </Box>
        
        <Divider sx={{ my: 3 }} />
        
        {/* Order Details */}
        <Box sx={{ mb: 4 }}>
          <Typography variant="h6" gutterBottom>
            Order Details
          </Typography>
          <Card variant="outlined">
            <CardContent>
              <Grid container spacing={2}>
                <Grid item xs={12}>
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <Typography variant="body2" color="text.secondary">
                      Order ID:
                    </Typography>
                    <Typography variant="body1" fontWeight="bold">
                      {orderDetails?.orderId}
                    </Typography>
                    <IconButton 
                      size="small" 
                      onClick={handleCopyOrderId}
                      sx={{ ml: 1 }}
                    >
                      <ContentCopy fontSize="small" />
                    </IconButton>
                    {copied && (
                      <Typography variant="caption" color="success.main">
                        Copied!
                      </Typography>
                    )}
                  </Box>
                </Grid>
                <Grid item xs={12}>
                  <Typography variant="body2" color="text.secondary">
                    Order Date:
                  </Typography>
                  <Typography variant="body1">
                    {new Date().toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric'
                    })}
                  </Typography>
                </Grid>
                <Grid item xs={12}>
                  <Typography variant="body2" color="text.secondary">
                    Total Amount:
                  </Typography>
                  <Typography variant="h5" color="primary">
                    ${orderDetails?.total}
                  </Typography>
                </Grid>
              </Grid>
            </CardContent>
          </Card>
        </Box>
        
        {/* What's Next */}
        <Box sx={{ mb: 4 }}>
          <Typography variant="h6" gutterBottom>
            What's Next?
          </Typography>
          <List>
            <ListItem>
              <ListItemText 
                primary="1. Order Processing"
                secondary="Your order is being prepared by our team"
              />
            </ListItem>
            <ListItem>
              <ListItemText 
                primary="2. Shipping Notification"
                secondary="You'll receive an email when your order ships"
              />
            </ListItem>
            <ListItem>
              <ListItemText 
                primary="3. Delivery"
                secondary="Track your package using the tracking number in your shipping email"
              />
            </ListItem>
          </List>
        </Box>
        
        <Divider sx={{ my: 3 }} />
        
        {/* Action Buttons */}
        <Box sx={{ 
          display: 'flex', 
          gap: 2, 
          flexWrap: 'wrap',
          justifyContent: 'center'
        }}>
          <Button
            variant="contained"
            startIcon={<ShoppingBag />}
            onClick={() => navigate('/profile', { state: { activeTab: 1 } })}
          >
            View My Orders
          </Button>
          <Button
            variant="outlined"
            startIcon={<Home />}
            onClick={() => navigate('/')}
          >
            Continue Shopping
          </Button>
          <Button
            variant="outlined"
            startIcon={<Print />}
            onClick={handlePrint}
            sx={{ display: { xs: 'none', sm: 'inline-flex' } }}
          >
            Print Order
          </Button>
        </Box>
        
        {/* Additional Info */}
        <Alert severity="info" sx={{ mt: 4 }}>
          <Typography variant="body2">
            Questions about your order? Contact our support team at info@leadergraphic.com or call (814) 528-5722.
          </Typography>
        </Alert>
      </Paper>
    </Container>
  );
};

export default OrderConfirmationPage;