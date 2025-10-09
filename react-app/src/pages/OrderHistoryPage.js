import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Box,
  Container,
  Paper,
  Typography,
  Grid,
  Stepper,
  Step,
  StepLabel,
  StepContent,
  List,
  ListItem,
  ListItemText,
  Divider,
  Button,
  Chip,
  Card,
  CardContent,
  IconButton,
  Skeleton,
  Alert,
  useTheme,
  useMediaQuery
} from '@mui/material';
import {
  ArrowBack,
  LocalShipping,
  Receipt,
  CheckCircle,
  Schedule,
  Print,
  Download,
  ContentCopy
} from '@mui/icons-material';
import api from '../services/api';

function OrderHistoryPage() {
  const { orderId } = useParams();
  const navigate = useNavigate();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  
  useEffect(() => {
    fetchOrderDetails();
  }, [orderId]);

  const fetchOrderDetails = async () => {
    try {
      const response = await api.get(`/orders/detail.php?id=${orderId}`);
      setOrder(response.data);
    } catch (error) {
      console.error('Failed to fetch order:', error);
      setError('Failed to load order details');
    } finally {
      setLoading(false);
    }
  };

  const getStatusStep = (status) => {
    const steps = ['pending', 'processing', 'shipped', 'delivered'];
    return steps.indexOf(status);
  };

  const handlePrint = () => {
    window.print();
  };

  const handleCopyOrderId = () => {
    // Copy the formatted order ID, not the database ID
    const orderNumberToCopy = order?.order_id || orderId;
    navigator.clipboard.writeText(orderNumberToCopy);
    // Show success message
  };

  if (loading) {
    return (
      <Container maxWidth="lg" sx={{ py: 4 }}>
        <Skeleton variant="rectangular" height={200} sx={{ mb: 2 }} />
        <Grid container spacing={3}>
          <Grid item xs={12} md={8}>
            <Skeleton variant="rectangular" height={400} />
          </Grid>
          <Grid item xs={12} md={4}>
            <Skeleton variant="rectangular" height={300} />
          </Grid>
        </Grid>
      </Container>
    );
  }

  if (error || !order) {
    return (
      <Container maxWidth="lg" sx={{ py: 4 }}>
        <Alert severity="error">{error || 'Order not found'}</Alert>
        <Button
          startIcon={<ArrowBack />}
          onClick={() => navigate('/profile')}
          sx={{ mt: 2 }}
        >
          Back to Profile
        </Button>
      </Container>
    );
  }

  return (
    <Container maxWidth="lg" sx={{ py: { xs: 2, md: 4 } }}>
      {/* Header */}
      <Box sx={{ mb: 3, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <IconButton onClick={() => navigate('/profile')} size="small">
            <ArrowBack />
          </IconButton>
          <Typography variant="h4" component="h1">
            Order Details
          </Typography>
        </Box>
        <Box sx={{ display: 'flex', gap: 1 }}>
          <IconButton onClick={handlePrint} size="small">
            <Print />
          </IconButton>
          <IconButton size="small">
            <Download />
          </IconButton>
        </Box>
      </Box>

      {/* Order Summary Card */}
      <Card sx={{ mb: 3, bgcolor: 'primary.main', color: 'white' }}>
        <CardContent>
          <Grid container spacing={2} alignItems="center">
            <Grid item xs={12} md={6}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 1 }}>
                <Typography variant="h5">
                  Order #{order.order_id || order.id}
                </Typography>
                <IconButton
                  size="small"
                  onClick={handleCopyOrderId}
                  sx={{ color: 'white' }}
                >
                  <ContentCopy fontSize="small" />
                </IconButton>
              </Box>
              <Typography variant="body2" sx={{ opacity: 0.9 }}>
                Placed on {new Date(order.created_at).toLocaleDateString('en-US', {
                  weekday: 'long',
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric'
                })}
              </Typography>
            </Grid>
            <Grid item xs={12} md={6} sx={{ textAlign: { md: 'right' } }}>
              <Chip
                label={order.status.toUpperCase()}
                sx={{
                  bgcolor: 'white',
                  color: 'primary.main',
                  fontWeight: 'bold'
                }}
              />
              {order.tracking_number && (
                <Typography variant="body2" sx={{ mt: 1 }}>
                  Tracking: {order.tracking_number}
                </Typography>
              )}
            </Grid>
          </Grid>
        </CardContent>
      </Card>

      <Grid container spacing={3}>
        {/* Main Content */}
        <Grid item xs={12} md={8}>
          {/* Order Status Timeline */}
          <Paper sx={{ p: 3, mb: 3 }}>
            <Typography variant="h6" gutterBottom>
              Order Status
            </Typography>
            <Stepper activeStep={getStatusStep(order.status)} orientation="vertical">
              <Step completed>
                <StepLabel
                  StepIconComponent={() => <Schedule color="action" />}
                >
                  Order Placed
                </StepLabel>
                <StepContent>
                  <Typography variant="body2" color="text.secondary">
                    {new Date(order.created_at).toLocaleString()}
                  </Typography>
                </StepContent>
              </Step>
              <Step completed={getStatusStep(order.status) >= 1}>
                <StepLabel
                  StepIconComponent={() => <Receipt color={getStatusStep(order.status) >= 1 ? 'action' : 'disabled'} />}
                >
                  Processing
                </StepLabel>
                <StepContent>
                  <Typography variant="body2" color="text.secondary">
                    Your order is being prepared
                  </Typography>
                </StepContent>
              </Step>
              <Step completed={getStatusStep(order.status) >= 2}>
                <StepLabel
                  StepIconComponent={() => <LocalShipping color={getStatusStep(order.status) >= 2 ? 'action' : 'disabled'} />}
                >
                  Shipped
                </StepLabel>
                <StepContent>
                  <Typography variant="body2" color="text.secondary">
                    {order.shipped_date && new Date(order.shipped_date).toLocaleString()}
                    {order.tracking_number && (
                      <Box sx={{ mt: 1 }}>
                        <Button size="small" variant="outlined">
                          Track Package
                        </Button>
                      </Box>
                    )}
                  </Typography>
                </StepContent>
              </Step>
              <Step completed={getStatusStep(order.status) >= 3}>
                <StepLabel
                  StepIconComponent={() => <CheckCircle color={getStatusStep(order.status) >= 3 ? 'success' : 'disabled'} />}
                >
                  Delivered
                </StepLabel>
                <StepContent>
                  <Typography variant="body2" color="text.secondary">
                    {order.delivered_date && new Date(order.delivered_date).toLocaleString()}
                  </Typography>
                </StepContent>
              </Step>
            </Stepper>
          </Paper>

          {/* Order Items */}
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" gutterBottom>
              Order Items
            </Typography>
            <List>
              {order.items?.map((item, index) => (
                <React.Fragment key={index}>
                  {index > 0 && <Divider />}
                  <ListItem sx={{ py: 2 }}>
                    <Grid container spacing={2} alignItems="center">
                      <Grid item xs={12} sm={6}>
                        <ListItemText
                          primary={item.name}
                          secondary={
                            <>
                              SKU: {item.sku}<br />
                              Size: {item.size} | Color: {item.color}
                              {item.logo && ` | Logo: ${item.logo}`}
                            </>
                          }
                        />
                      </Grid>
                      <Grid item xs={6} sm={3}>
                        <Typography variant="body2" color="text.secondary">
                          Qty: {item.quantity}
                        </Typography>
                        <Typography variant="body2">
                          ${item.price.toFixed(2)} each
                        </Typography>
                      </Grid>
                      <Grid item xs={6} sm={3} sx={{ textAlign: 'right' }}>
                        <Typography variant="h6">
                          ${(item.price * item.quantity).toFixed(2)}
                        </Typography>
                      </Grid>
                    </Grid>
                  </ListItem>
                </React.Fragment>
              ))}
            </List>
          </Paper>
        </Grid>

        {/* Sidebar */}
        <Grid item xs={12} md={4}>
          {/* Delivery Address */}
          <Paper sx={{ p: 3, mb: 3 }}>
            <Typography variant="h6" gutterBottom>
              Delivery Address
            </Typography>
            <Typography variant="body2">
              {order.shipping_address?.name}<br />
              {order.shipping_address?.address}<br />
              {order.shipping_address?.address2 && (
                <>{order.shipping_address.address2}<br /></>
              )}
              {order.shipping_address?.city}, {order.shipping_address?.state} {order.shipping_address?.zip}<br />
              {order.shipping_address?.phone}
            </Typography>
          </Paper>

          {/* Payment Summary */}
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" gutterBottom>
              Payment Summary
            </Typography>
            <Box sx={{ mb: 2 }}>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                <Typography variant="body2">Subtotal</Typography>
                <Typography variant="body2">${order.subtotal?.toFixed(2)}</Typography>
              </Box>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                <Typography variant="body2">Shipping</Typography>
                <Typography variant="body2">
                  {order.shipping_cost === 0 ? 'Free' : `$${order.shipping_cost?.toFixed(2)}`}
                </Typography>
              </Box>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                <Typography variant="body2">Tax</Typography>
                <Typography variant="body2">${order.tax?.toFixed(2)}</Typography>
              </Box>
            </Box>
            <Divider sx={{ my: 2 }} />
            <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
              <Typography variant="h6">Total</Typography>
              <Typography variant="h6" color="primary">
                ${order.total?.toFixed(2)}
              </Typography>
            </Box>
          </Paper>

          {/* Actions */}
          <Box sx={{ mt: 3 }}>
            {order.status === 'delivered' && (
              <Button fullWidth variant="outlined" sx={{ mb: 1 }}>
                Leave a Review
              </Button>
            )}
            {order.status !== 'cancelled' && order.status !== 'delivered' && (
              <Button fullWidth variant="outlined" color="error">
                Cancel Order
              </Button>
            )}
          </Box>
        </Grid>
      </Grid>
    </Container>
  );
}

export default OrderHistoryPage;