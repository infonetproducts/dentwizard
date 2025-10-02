import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useSelector, useDispatch } from 'react-redux';
import {
  Box,
  Container,
  Typography,
  Card,
  CardContent,
  Button,
  Stack,
  Divider,
  List,
  ListItem,
  ListItemText,
  ListItemAvatar,
  Avatar,
  IconButton,
  useTheme,
  useMediaQuery,
  Paper
} from '@mui/material';
import {
  Delete as DeleteIcon,
  Add as AddIcon,
  Remove as RemoveIcon,
  ShoppingCart as CartIcon,
  ArrowBack
} from '@mui/icons-material';
import { fetchCart, updateQuantity, removeFromCart } from '../store/slices/cartSlice';
import toast from 'react-hot-toast';

const CartPage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  
  const { items, summary } = useSelector(state => state.cart);
  const { budget } = useSelector(state => state.auth);
  
  // Ensure cart is loaded when page mounts
  React.useEffect(() => {
    dispatch(fetchCart());
  }, [dispatch]);
  
  const handleQuantityChange = async (itemId, newQuantity) => {
    if (newQuantity < 1) return;
    await dispatch(updateQuantity({ itemId, quantity: newQuantity }));
    dispatch(fetchCart());
  };

  const handleRemoveItem = async (itemId) => {
    await dispatch(removeFromCart({ itemId }));
    await dispatch(fetchCart());  // Fetch updated cart after removal
    toast.success('Item removed from cart');
  };

  if (!items || items.length === 0) {
    return (
      <Container maxWidth="sm" sx={{ py: 8, textAlign: 'center' }}>
        <CartIcon sx={{ fontSize: 80, color: 'grey.400', mb: 2 }} />
        <Typography variant="h5" gutterBottom>
          Your cart is empty
        </Typography>
        <Typography variant="body1" color="text.secondary" paragraph>
          Add items to your cart to see them here.
        </Typography>
        <Button
          variant="contained"
          size="large"
          onClick={() => navigate('/products')}
        >
          Start Shopping
        </Button>
      </Container>
    );
  }

  return (
    <Container maxWidth="lg" sx={{ py: isMobile ? 2 : 4 }}>
      <Button
        startIcon={<ArrowBack />}
        onClick={() => navigate('/products')}
        sx={{ mb: 2 }}
      >
        Continue Shopping
      </Button>
      
      <Typography variant={isMobile ? 'h5' : 'h4'} fontWeight="bold" gutterBottom>
        Shopping Cart ({summary?.total_items || 0} items)
      </Typography>

      <Box sx={{ display: { xs: 'block', md: 'flex' }, gap: 3, mt: 3 }}>
        {/* Cart Items */}
        <Box sx={{ flex: 1 }}>
          <Card>
            <CardContent>
              <List>
                {items.map((item, index) => (
                  <React.Fragment key={item.id}>
                    {index > 0 && <Divider />}
                    <ListItem
                      sx={{ py: 2 }}
                      secondaryAction={
                        <IconButton 
                          edge="end" 
                          color="error"
                          onClick={() => handleRemoveItem(item.id)}
                        >
                          <DeleteIcon />
                        </IconButton>
                      }
                    >
                      <ListItemAvatar>
                        <Avatar
                          src={(() => {
                            let imageUrl = item.image || item.image_url || '';
                            
                            // For Polished items, modify the image URL
                            if (item.color === 'Polished' && imageUrl) {
                              // Check if it's a DentWizard product image
                              if (imageUrl.includes('/CB35410') && !imageUrl.includes('CB35410P')) {
                                // Replace CB35410.jpg with CB35410P.jpg
                                imageUrl = imageUrl.replace('CB35410.jpg', 'CB35410P.jpg');
                              } else if (imageUrl.includes('.jpg') && !imageUrl.includes('P.jpg')) {
                                // Generic approach: add P before extension
                                imageUrl = imageUrl.replace('.jpg', 'P.jpg');
                              }
                            }
                            
                            return imageUrl;
                          })()}
                          variant="rounded"
                          sx={{ width: 80, height: 80, mr: 2 }}
                        >
                          {item.name?.charAt(0)}
                        </Avatar>
                      </ListItemAvatar>
                      <ListItemText
                        primary={
                          <Typography variant="h6" fontWeight="500">
                            {item.name}
                          </Typography>
                        }
                        secondary={
                          <Box>
                            <Typography variant="body2" color="text.secondary">
                              ${item.price} each
                            </Typography>
                            {(item.size || item.color || item.logo) && (
                              <Typography variant="body2" color="text.secondary">
                                {item.size && `Size: ${item.size}`}
                                {item.size && item.color && ' | '}
                                {item.color && `Color: ${item.color}`}
                                {(item.size || item.color) && item.logo && ' | '}
                                {item.logo && `Logo: ${item.logo}`}
                              </Typography>
                            )}
                            <Stack direction="row" alignItems="center" spacing={1} sx={{ mt: 1 }}>
                              <IconButton
                                size="small"
                                onClick={() => handleQuantityChange(item.id, item.quantity - 1)}
                              >
                                <RemoveIcon />
                              </IconButton>
                              <Typography sx={{ px: 2 }}>{item.quantity}</Typography>
                              <IconButton
                                size="small"
                                onClick={() => handleQuantityChange(item.id, item.quantity + 1)}
                              >
                                <AddIcon />
                              </IconButton>
                            </Stack>
                          </Box>
                        }
                      />
                      <Typography variant="h6" sx={{ ml: 2 }}>
                        ${(item.price * item.quantity).toFixed(2)}
                      </Typography>
                    </ListItem>
                  </React.Fragment>
                ))}
              </List>
            </CardContent>
          </Card>
        </Box>

        {/* Order Summary */}
        <Box sx={{ width: { xs: '100%', md: 350 }, mt: { xs: 3, md: 0 } }}>
          <Card>
            <CardContent>
              <Typography variant="h6" fontWeight="bold" gutterBottom>
                Order Summary
              </Typography>
              
              <Stack spacing={2} sx={{ mt: 2 }}>
                <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Typography>Subtotal:</Typography>
                  <Typography fontWeight="500">${summary?.subtotal?.toFixed(2)}</Typography>
                </Box>
                <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Typography>Tax:</Typography>
                  <Typography color="text.secondary" variant="body2">Calculated at checkout</Typography>
                </Box>
                <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Typography>Shipping:</Typography>
                  <Typography>${summary?.shipping?.toFixed(2)}</Typography>
                </Box>
                
                <Divider />
                
                <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Typography variant="h6">Total:</Typography>
                  <Typography variant="h6" color="primary" fontWeight="bold">
                    ${summary?.total?.toFixed(2)}
                  </Typography>
                </Box>

                {budget?.has_budget && (
                  <Paper sx={{ p: 2, bgcolor: budget.remaining >= summary.total ? 'success.light' : 'error.light' }}>
                    <Typography variant="body2">
                      Budget Remaining: ${budget.remaining?.toFixed(2)}
                    </Typography>
                    <Typography variant="caption">
                      {budget.remaining >= summary.total 
                        ? 'Within budget ✓' 
                        : 'Over budget - Approval required'}
                    </Typography>
                  </Paper>
                )}

                <Button
                  variant="contained"
                  size="large"
                  fullWidth
                  onClick={() => navigate('/checkout')}
                  sx={{ mt: 2 }}
                >
                  Proceed to Checkout
                </Button>
              </Stack>
            </CardContent>
          </Card>
        </Box>
      </Box>
    </Container>
  );
};

export default CartPage;