import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Container,
  Grid,
  Typography,
  Button,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Stack,
  Chip,
  Paper,
  Skeleton,
  useTheme,
  useMediaQuery,
  IconButton,
  Box,
  Divider,
  ToggleButtonGroup,
  ToggleButton
} from '@mui/material';
import {
  ShoppingCart,
  ArrowBack,
  Add,
  Remove,
  LocalShipping,
  Security,
  CheckCircle
} from '@mui/icons-material';
import { useDispatch } from 'react-redux';
import { addToCart, fetchCart } from '../store/slices/cartSlice';
import api from '../services/api';
import toast from 'react-hot-toast';

const ProductDetailPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);
  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [availableSizes, setAvailableSizes] = useState([]);
  
  // Available colors - could come from database
  const colors = [
    { name: 'Navy', value: '#000080' },
    { name: 'Black', value: '#000000' },
    { name: 'Gray', value: '#808080' },
    { name: 'White', value: '#FFFFFF' },
    { name: 'Royal Blue', value: '#4169E1' }
  ];

  useEffect(() => {
    loadProduct();
  }, [id]);

  const loadProduct = async () => {
    try {
      const response = await api.get(`/products/detail.php?id=${id}`);
      setProduct(response.data.data);
      // Set available sizes from API
      if (response.data.data && response.data.data.available_sizes) {
        setAvailableSizes(response.data.data.available_sizes);
        // Set default size to first available
        setSelectedSize(response.data.data.available_sizes[0] || '');
      }
      // Set default color
      setSelectedColor(colors[0].name);
    } catch (error) {
      toast.error('Failed to load product');
      navigate('/products');
    } finally {
      setLoading(false);
    }
  };

  const handleQuantityChange = (change) => {
    const newQuantity = quantity + change;
    if (newQuantity > 0 && newQuantity <= 10) {
      setQuantity(newQuantity);
    }
  };

  const handleAddToCart = async () => {
    if (!selectedSize) {
      toast.error('Please select a size');
      return;
    }
    
    try {
      await dispatch(addToCart({
        productId: product.id,
        quantity,
        options: {
          size: selectedSize,
          color: selectedColor
        }
      })).unwrap();
      
      await dispatch(fetchCart());
      toast.success(`${product.name} added to cart!`);
    } catch (error) {
      toast.error('Failed to add to cart');
    }
  };

  if (loading) {
    return (
      <Container maxWidth="lg" sx={{ py: 4 }}>
        <Grid container spacing={4}>
          <Grid item xs={12} md={6}>
            <Skeleton variant="rectangular" height={500} />
          </Grid>
          <Grid item xs={12} md={6}>
            <Skeleton variant="text" height={60} />
            <Skeleton variant="text" />
            <Skeleton variant="text" />
            <Skeleton variant="rectangular" height={200} sx={{ mt: 2 }} />
          </Grid>
        </Grid>
      </Container>
    );
  }

  if (!product) return null;

  return (
    <Container maxWidth="lg" sx={{ py: isMobile ? 2 : 4 }}>
      <Button
        startIcon={<ArrowBack />}
        onClick={() => navigate('/products')}
        sx={{ mb: 3 }}
        variant="text"
      >
        Back to Products
      </Button>
      
      <Grid container spacing={4}>
        {/* Product Image */}
        <Grid item xs={12} md={6}>
          <Paper elevation={0} sx={{ position: 'relative', bgcolor: '#f5f5f5' }}>
            <img
              src={product.image_url || '/placeholder.png'}
              alt={product.name}
              style={{
                width: '100%',
                height: isMobile ? 400 : 600,
                objectFit: 'contain'
              }}
            />
            {product.sale_price && (
              <Chip
                label="SALE"
                color="error"
                sx={{
                  position: 'absolute',
                  top: 16,
                  left: 16
                }}
              />
            )}
          </Paper>
        </Grid>

        {/* Product Info */}
        <Grid item xs={12} md={6}>
          <Stack spacing={3}>
            {/* Title and Price */}
            <Box>
              <Typography variant={isMobile ? 'h4' : 'h3'} fontWeight="600" gutterBottom>
                {product.name}
              </Typography>
              
              <Stack direction="row" alignItems="baseline" spacing={2}>
                <Typography variant="h4" color="primary" fontWeight="bold">
                  ${Number(product.sale_price || product.price).toFixed(2)}
                </Typography>
                {product.sale_price && product.price > product.sale_price && (
                  <Typography
                    variant="h5"
                    color="text.secondary"
                    sx={{ textDecoration: 'line-through' }}
                  >
                    ${Number(product.price).toFixed(2)}
                  </Typography>
                )}
              </Stack>
              
              {product.sku && (
                <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
                  SKU: {product.sku}
                </Typography>
              )}
            </Box>

            <Divider />

            {/* Color Selection */}
            <Box>
              <Typography variant="subtitle1" fontWeight="600" gutterBottom>
                Color: {selectedColor}
              </Typography>
              <Stack direction="row" spacing={1} flexWrap="wrap">
                {colors.map((color) => (
                  <IconButton
                    key={color.name}
                    onClick={() => setSelectedColor(color.name)}
                    sx={{
                      border: selectedColor === color.name ? 2 : 1,
                      borderColor: selectedColor === color.name ? 'primary.main' : 'grey.300',
                      borderRadius: 1,
                      p: 0.5
                    }}
                  >
                    <Box
                      sx={{
                        width: 40,
                        height: 40,
                        bgcolor: color.value,
                        border: 1,
                        borderColor: 'grey.400',
                        borderRadius: 0.5
                      }}
                    />
                  </IconButton>
                ))}
              </Stack>
            </Box>

            {/* Size Selection */}
            <Box>
              <Typography variant="subtitle1" fontWeight="600" gutterBottom>
                Size
              </Typography>
              <ToggleButtonGroup
                value={selectedSize}
                exclusive
                onChange={(e, newSize) => newSize && setSelectedSize(newSize)}
                aria-label="size selection"
              >
                {availableSizes.map((size) => (
                  <ToggleButton
                    key={size}
                    value={size}
                    sx={{
                      px: 2,
                      py: 1,
                      minWidth: 50
                    }}
                  >
                    {size}
                  </ToggleButton>
                ))}
              </ToggleButtonGroup>
            </Box>

            {/* Quantity Selection */}
            <Box>
              <Typography variant="subtitle1" fontWeight="600" gutterBottom>
                Quantity
              </Typography>
              <Stack direction="row" spacing={2} alignItems="center">
                <IconButton
                  onClick={() => handleQuantityChange(-1)}
                  disabled={quantity <= 1}
                  sx={{ border: 1, borderColor: 'grey.300' }}
                >
                  <Remove />
                </IconButton>
                <Typography variant="h6" sx={{ minWidth: 40, textAlign: 'center' }}>
                  {quantity}
                </Typography>
                <IconButton
                  onClick={() => handleQuantityChange(1)}
                  disabled={quantity >= 10}
                  sx={{ border: 1, borderColor: 'grey.300' }}
                >
                  <Add />
                </IconButton>
              </Stack>
            </Box>

            {/* Add to Cart Button */}
            <Button
              variant="contained"
              size="large"
              fullWidth
              startIcon={<ShoppingCart />}
              onClick={handleAddToCart}
              sx={{ py: 1.5 }}
            >
              Add to Cart
            </Button>

            {/* Product Features */}
            <Stack spacing={2} sx={{ mt: 2 }}>
              <Stack direction="row" spacing={1} alignItems="center">
                <LocalShipping color="action" />
                <Typography variant="body2">
                  Free shipping on orders over $100
                </Typography>
              </Stack>
              <Stack direction="row" spacing={1} alignItems="center">
                <Security color="action" />
                <Typography variant="body2">
                  Secure checkout with SSL encryption
                </Typography>
              </Stack>
              <Stack direction="row" spacing={1} alignItems="center">
                <CheckCircle color="action" />
                <Typography variant="body2">
                  In stock and ready to ship
                </Typography>
              </Stack>
            </Stack>

            {/* Product Description */}
            {product.description && (
              <Box>
                <Typography variant="h6" fontWeight="600" gutterBottom>
                  Description
                </Typography>
                <Typography variant="body1" color="text.secondary">
                  {product.description}
                </Typography>
              </Box>
            )}

            {/* Additional Details */}
            <Box>
              <Typography variant="h6" fontWeight="600" gutterBottom>
                Product Details
              </Typography>
              <Stack spacing={1}>
                <Typography variant="body2" color="text.secondary">
                  • 100% Polyester performance fabric
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  • Moisture-wicking and breathable
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  • Machine washable
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  • Embroidered logo
                </Typography>
              </Stack>
            </Box>
          </Stack>
        </Grid>
      </Grid>
    </Container>
  );
};

export default ProductDetailPage;