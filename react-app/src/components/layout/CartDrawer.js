import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import {
  Drawer,
  Box,
  Typography,
  IconButton,
  List,
  ListItem,
  ListItemText,
  ListItemAvatar,
  Avatar,
  Button,
  Divider,
  Stack,
  Chip
} from '@mui/material';
import {
  Close as CloseIcon,
  ShoppingBag as ShoppingBagIcon,
  Delete as DeleteIcon
} from '@mui/icons-material';
import { closeCartDrawer } from '../../store/slices/uiSlice';
import { fetchCart } from '../../store/slices/cartSlice';

const CartDrawer = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { isCartDrawerOpen } = useSelector(state => state.ui);
  const { items, summary } = useSelector(state => state.cart);

  const handleCheckout = () => {
    dispatch(closeCartDrawer());
    navigate('/checkout');
  };

  const handleClose = () => {
    dispatch(closeCartDrawer());
  };

  return (
    <Drawer
      anchor="right"
      open={isCartDrawerOpen}
      onClose={handleClose}
      sx={{
        '& .MuiDrawer-paper': {
          width: { xs: '100%', sm: 400 },
          maxWidth: '100%'
        }
      }}
    >
      <Box sx={{ p: 2 }}>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
          <Typography variant="h6" fontWeight="bold">
            Shopping Cart ({summary?.total_items || 0})
          </Typography>
          <IconButton onClick={handleClose}>
            <CloseIcon />
          </IconButton>
        </Box>

        <Divider />

        {items.length === 0 ? (
          <Box sx={{ py: 8, textAlign: 'center' }}>
            <ShoppingBagIcon sx={{ fontSize: 64, color: 'grey.400', mb: 2 }} />
            <Typography variant="body1" color="text.secondary">
              Your cart is empty
            </Typography>
            <Button
              variant="contained"
              onClick={() => {
                handleClose();
                navigate('/products');
              }}
              sx={{ mt: 2 }}
            >
              Start Shopping
            </Button>
          </Box>
        ) : (
          <>
            <List sx={{ flexGrow: 1, overflow: 'auto', maxHeight: 'calc(100vh - 300px)' }}>
              {items.map((item) => (
                <ListItem
                  key={item.id}
                  secondaryAction={
                    <IconButton edge="end" aria-label="delete">
                      <DeleteIcon />
                    </IconButton>
                  }
                >
                  <ListItemAvatar>
                    <Avatar src={item.image} variant="rounded">
                      {item.name?.charAt(0)}
                    </Avatar>
                  </ListItemAvatar>
                  <ListItemText
                    primary={item.name}
                    secondary={
                      <Stack direction="row" spacing={1} alignItems="center">
                        <Typography variant="body2">
                          ${item.price} × {item.quantity}
                        </Typography>
                        {item.options && (
                          <Chip label={item.options} size="small" />
                        )}
                      </Stack>
                    }
                  />
                </ListItem>
              ))}
            </List>

            <Divider sx={{ my: 2 }} />

            <Stack spacing={1}>
              <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                <Typography>Subtotal:</Typography>
                <Typography fontWeight="bold">${summary?.subtotal?.toFixed(2)}</Typography>
              </Box>
              <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                <Typography>Tax:</Typography>
                <Typography>${summary?.tax?.toFixed(2)}</Typography>
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
            </Stack>

            <Button
              fullWidth
              variant="contained"
              size="large"
              onClick={handleCheckout}
              sx={{ mt: 3 }}
            >
              Proceed to Checkout
            </Button>
          </>
        )}
      </Box>
    </Drawer>
  );
};

export default CartDrawer;
