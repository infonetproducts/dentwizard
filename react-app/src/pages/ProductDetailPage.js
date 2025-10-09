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
  const [colorVariants, setColorVariants] = useState([]);
  const [currentImage, setCurrentImage] = useState('');
  const [currentPrice, setCurrentPrice] = useState(0);
  const [selectedVariantId, setSelectedVariantId] = useState(null);
  const [availableLogos, setAvailableLogos] = useState([]);
  const [selectedLogo, setSelectedLogo] = useState('');
  const [hasLogos, setHasLogos] = useState(false);
  useEffect(() => {
    loadProduct();
  }, [id]);

  const loadProduct = async () => {
    // Validate product ID before making API call
    if (!id || id === 'undefined' || id === 'null') {
      console.error('Invalid product ID:', id);
      navigate('/products', { replace: true });
      return;
    }
    
    try {
      const response = await api.get(`/products/detail.php?id=${id}`);
      const productData = response.data.data;
      
      setProduct(productData);
      
      // Set available sizes from API
      if (productData && productData.available_sizes) {
        setAvailableSizes(productData.available_sizes);
        setSelectedSize(productData.available_sizes[0] || '');
      }
      
      // Set color variants from API
      if (productData && productData.color_variants && productData.color_variants.length > 0) {
        setColorVariants(productData.color_variants);
        
        // Set default to first color variant
        const firstVariant = productData.color_variants[0];
        setSelectedColor(firstVariant.name);
        setCurrentImage(firstVariant.image || productData.image_url);
        setCurrentPrice(firstVariant.price || productData.price);
        setSelectedVariantId(firstVariant.id);
      } else {
        // No color variants - use default product data
        setCurrentImage(productData.image_url);
        setCurrentPrice(productData.price);
      }
      
      // Set available logos from API
      if (productData && productData.available_logos) {
        setAvailableLogos(productData.available_logos);
        setHasLogos(productData.has_logos || false);
        setSelectedLogo(''); // Default to no selection
        console.log('Logos found:', productData.available_logos);
      } else {
        console.log('No logos in API response');
      }
    } catch (error) {
      console.error('Product load error:', error);
      // Only show toast for real API errors, not validation errors
      if (id && id !== 'undefined' && id !== 'null') {
        toast.error('Failed to load product');
      }
      navigate('/products', { replace: true });
    } finally {
      setLoading(false);
    }
  };

  const handleColorSelect = (variant) => {
    setSelectedColor(variant.name);
    setCurrentImage(variant.image);
    setCurrentPrice(variant.price);
    setSelectedVariantId(variant.id);
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
    
    if (colorVariants.length > 0 && !selectedColor) {
      toast.error('Please select a color');
      return;
    }
    
    // Check if logo is required (if logos are available but none selected)
    if (hasLogos && availableLogos.length > 0 && !selectedLogo) {
      toast.error('Please select a logo');
      return;
    }
    
    try {
      await dispatch(addToCart({
        productId: product.id, // Always use main product ID
        quantity,
        options: {
          size: selectedSize,
          color: selectedColor,
          variantId: selectedVariantId, // Include variant ID for tracking
          image: currentImage, // Pass the current image URL
          logo: selectedLogo // Include selected logo
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
          <Paper elevation={0} sx={{ position: 'relative', bgcolor: '#ffffff' }}>
            <img
              src={currentImage || product.image_url || '/placeholder.png'}
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
            {colorVariants.length > 1 && (
              <Box>
                <Typography variant="subtitle1" fontWeight="600" gutterBottom>
                  Color: {selectedColor}
                </Typography>
                <Box 
                  sx={{ 
                    display: 'flex', 
                    flexWrap: 'wrap', 
                    gap: 1,
                    '& > *': {
                      mb: 1
                    }
                  }}
                >
                  {colorVariants.map((variant) => (
                    <Button
                      key={variant.id}
                      variant={selectedColor === variant.name ? "contained" : "outlined"}
                      onClick={() => handleColorSelect(variant)}
                      sx={{
                        minWidth: 100,
                        textTransform: 'none'
                      }}
                    >
                      {variant.name}
                    </Button>
                  ))}
                </Box>
              </Box>
            )}

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

            {/* Logo Selection */}
            {hasLogos && availableLogos.length > 0 && (
              <Box>
                <Typography variant="subtitle1" fontWeight="600" gutterBottom>
                  Artwork (Choose Logo Option)
                </Typography>
                <Typography variant="caption" color="text.secondary" display="block" sx={{ mb: 1 }}>
                  The logo cannot be previewed on this product but will be applied to the product on your order.
                </Typography>
                <FormControl fullWidth sx={{ maxWidth: 300 }}>
                  <Select
                    value={selectedLogo}
                    onChange={(e) => setSelectedLogo(e.target.value)}
                    displayEmpty
                    sx={{ bgcolor: 'background.paper' }}
                  >
                    <MenuItem value="">
                      <em>PLEASE SELECT LOGO</em>
                    </MenuItem>
                    {availableLogos.map((logo) => (
                      <MenuItem key={logo.id} value={logo.name}>
                        {logo.name}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
                
                {/* Logo Image Preview */}
                {selectedLogo && (
                  <Box sx={{ mt: 2 }}>
                    {(() => {
                      const selectedLogoData = availableLogos.find(logo => logo.name === selectedLogo);
                      if (selectedLogoData && selectedLogoData.image) {
                        return (
                          <img 
                            src={selectedLogoData.image} 
                            alt={selectedLogo}
                            style={{ 
                              maxWidth: '200px', 
                              maxHeight: '150px',
                              display: 'block',
                              border: '1px solid #e0e0e0',
                              padding: '10px',
                              backgroundColor: '#fff'
                            }}
                          />
                        );
                      }
                      return null;
                    })()}
                  </Box>
                )}
              </Box>
            )}

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
                <Box>
                  {(() => {
                    // Process description text
                    const lines = product.description
                      .replace(/\\r\\n/g, '\n')  // Convert literal \r\n to actual line breaks
                      .split('\n')
                      .filter(line => line.trim());
                    
                    // Group lines - join continuation lines with bullets
                    const processedLines = [];
                    let currentBullet = null;
                    
                    lines.forEach(line => {
                      const trimmedLine = line.trim();
                      const isBullet = trimmedLine.match(/^[-•]/);
                      
                      if (isBullet) {
                        // Start new bullet point
                        if (currentBullet) {
                          processedLines.push(currentBullet);
                        }
                        currentBullet = trimmedLine;
                      } else if (currentBullet && !isBullet && trimmedLine.length > 0) {
                        // This is a continuation of the previous bullet
                        currentBullet += ' ' + trimmedLine;
                      } else {
                        // Regular paragraph line
                        if (currentBullet) {
                          processedLines.push(currentBullet);
                          currentBullet = null;
                        }
                        processedLines.push(trimmedLine);
                      }
                    });
                    
                    // Don't forget the last bullet if there is one
                    if (currentBullet) {
                      processedLines.push(currentBullet);
                    }
                    
                    return processedLines.map((line, index) => {
                      const isBullet = line.match(/^[-•]/);
                      
                      if (isBullet) {
                        return (
                          <Typography 
                            key={index} 
                            variant="body1" 
                            color="text.secondary"
                            sx={{ 
                              pl: 2, 
                              mb: 0.5,
                              display: 'flex',
                              alignItems: 'flex-start'
                            }}
                          >
                            <span style={{ marginRight: '8px' }}>•</span>
                            <span>{line.replace(/^[-•]\s*/, '').trim()}</span>
                          </Typography>
                        );
                      }
                      
                      return (
                        <Typography 
                          key={index} 
                          variant="body1" 
                          color="text.secondary"
                          sx={{ mb: 1.5 }}
                        >
                          {line}
                        </Typography>
                      );
                    });
                  })()}
                </Box>
              </Box>
            )}
          </Stack>
        </Grid>
      </Grid>
    </Container>
  );
};

export default ProductDetailPage;