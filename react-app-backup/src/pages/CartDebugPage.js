import React, { useState, useEffect } from 'react';
import { Container, Paper, Typography, Button, Box, Table, TableBody, TableCell, TableContainer, TableHead, TableRow } from '@mui/material';

const CartDebugPage = () => {
  const [cartData, setCartData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchCart = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch('/lg/API/v1/cart/cart.php?action=get', {
        credentials: 'include'
      });
      const data = await response.json();
      setCartData(data);
    } catch (err) {
      setError(err.message);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchCart();
  }, []);

  const addTestItem = async () => {
    try {
      const response = await fetch('/lg/API/v1/cart/cart.php?action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          product_id: 91754,
          quantity: 1,
          size: 'XLT',
          color: 'Atlas'
        })
      });
      await response.json();
      fetchCart();
    } catch (err) {
      console.error('Add error:', err);
    }
  };

  const clearCart = async () => {
    try {
      await fetch('/lg/API/v1/cart/cart.php?action=clear', {
        method: 'POST',
        credentials: 'include'
      });
      fetchCart();
    } catch (err) {
      console.error('Clear error:', err);
    }
  };

  return (
    <Container maxWidth="lg" sx={{ py: 4 }}>
      <Paper sx={{ p: 3 }}>
        <Typography variant="h4" gutterBottom>Cart Debug Page</Typography>
        
        <Box sx={{ my: 2 }}>
          <Button variant="contained" onClick={fetchCart} sx={{ mr: 2 }}>
            Refresh Cart
          </Button>
          <Button variant="outlined" onClick={addTestItem} sx={{ mr: 2 }}>
            Add Test Item
          </Button>
          <Button variant="outlined" color="error" onClick={clearCart}>
            Clear Cart
          </Button>
        </Box>

        {loading && <Typography>Loading...</Typography>}
        {error && <Typography color="error">Error: {error}</Typography>}
        
        {cartData && (
          <Box>
            <Typography variant="h6" gutterBottom>Cart Items:</Typography>
            {cartData.data?.cart_items?.length > 0 ? (
              <TableContainer component={Paper} variant="outlined">
                <Table>
                  <TableHead>
                    <TableRow>
                      <TableCell>ID</TableCell>
                      <TableCell>Product ID</TableCell>
                      <TableCell>Name</TableCell>
                      <TableCell>Size</TableCell>
                      <TableCell>Color</TableCell>
                      <TableCell>Quantity</TableCell>
                      <TableCell>Price</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {cartData.data.cart_items.map((item, index) => (
                      <TableRow key={index}>
                        <TableCell>{item.id}</TableCell>
                        <TableCell>{item.product_id}</TableCell>
                        <TableCell>{item.name}</TableCell>
                        <TableCell>{item.size}</TableCell>
                        <TableCell>{item.color}</TableCell>
                        <TableCell>{item.quantity}</TableCell>
                        <TableCell>${item.price}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            ) : (
              <Typography>Cart is empty</Typography>
            )}
            
            <Typography variant="h6" sx={{ mt: 3 }}>Cart Summary:</Typography>
            <Box sx={{ pl: 2 }}>
              <Typography>Total Items: {cartData.data?.cart_summary?.total_items || 0}</Typography>
              <Typography>Unique Items: {cartData.data?.cart_summary?.unique_items || 0}</Typography>
              <Typography>Subtotal: ${cartData.data?.cart_summary?.subtotal || 0}</Typography>
              <Typography>Tax: ${cartData.data?.cart_summary?.tax || 0}</Typography>
              <Typography>Shipping: ${cartData.data?.cart_summary?.shipping || 0}</Typography>
              <Typography>Total: ${cartData.data?.cart_summary?.total || 0}</Typography>
            </Box>
            
            <Typography variant="h6" sx={{ mt: 3 }}>Raw JSON:</Typography>
            <Box component="pre" sx={{ 
              bgcolor: '#f5f5f5', 
              p: 2, 
              borderRadius: 1,
              overflow: 'auto',
              fontSize: '12px'
            }}>
              {JSON.stringify(cartData, null, 2)}
            </Box>
          </Box>
        )}
      </Paper>
    </Container>
  );
};

export default CartDebugPage;