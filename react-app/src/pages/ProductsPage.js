import React, { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useSelector, useDispatch } from 'react-redux';
import {
  Box,
  Grid,
  Card,
  CardMedia,
  CardContent,
  Typography,
  Button,
  Chip,
  Stack,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Skeleton,
  IconButton,
  Container,
  Badge,
  useTheme,
  useMediaQuery,
  Breadcrumbs,
  Link
} from '@mui/material';
import {
  GridView as GridIcon,
  ViewList as ListIcon,
  Add as AddIcon
} from '@mui/icons-material';
import { motion, AnimatePresence } from 'framer-motion';
import { fetchProducts, setFilters } from '../store/slices/productsSlice';
import { addToCart, fetchCart } from '../store/slices/cartSlice';
import { setViewMode } from '../store/slices/uiSlice';
import CategoryNav from '../components/CategoryNav';
import SearchBar from '../components/SearchBar';
import toast from 'react-hot-toast';

const ProductsPage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const [searchParams, setSearchParams] = useSearchParams();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  
  const { items: products, loading, categories, filters } = useSelector(state => state.products);
  const { viewMode } = useSelector(state => state.ui);
  const cart = useSelector(state => state.cart.items);
  
  const categoryId = searchParams.get('category');
  const searchQuery = searchParams.get('search');
  const [sortBy, setSortBy] = useState('name');
  
  // Get category info
  const category = categories.find(cat => cat.id === parseInt(categoryId));
  const parentCategory = category?.parent_id ? 
    categories.find(cat => cat.id === category.parent_id) : null;
  
  useEffect(() => {
    dispatch(fetchProducts({ 
      category: categoryId || null,
      search: searchQuery || filters.search 
    }));
  }, [dispatch, categoryId, searchQuery, filters.search]);
  
  useEffect(() => {
    dispatch(fetchCart());
  }, [dispatch]);
  
  const handleAddToCart = (product) => {
    dispatch(addToCart({ productId: product.id, quantity: 1 }));
    toast.success(`${product.name} added to cart!`);
  };
  
  const handleSortChange = (value) => {
    setSortBy(value);
    // Sort products locally
    let sorted = [...products];
    switch(value) {
      case 'price_low':
        sorted.sort((a, b) => a.price - b.price);
        break;
      case 'price_high':
        sorted.sort((a, b) => b.price - a.price);
        break;
      case 'name':
      default:
        sorted.sort((a, b) => a.name.localeCompare(b.name));
    }
    // You could dispatch this to Redux store if needed
  };
  
  const getCartQuantity = (productId) => {
    const item = cart.find(item => item.product_id === productId);
    return item ? item.quantity : 0;
  };
  
  const ProductCard = ({ product }) => (
    <Card
      sx={{
        height: '100%',
        display: 'flex',
        flexDirection: viewMode === 'list' ? 'row' : 'column',
        cursor: 'pointer',
        '&:hover': {
          transform: 'translateY(-2px)',
          boxShadow: 3
        },
        transition: 'all 0.2s'
      }}
    >
      <CardMedia
        component="img"
        image={product.image_url || '/placeholder.png'}
        alt={product.name}
        onClick={() => navigate(`/products/${product.id}`)}
        sx={{
          width: viewMode === 'list' && !isMobile ? 200 : '100%',
          height: viewMode === 'list' && !isMobile ? 180 : (isMobile ? 180 : 260),
          objectFit: 'cover'
        }}
      />
      <CardContent sx={{ flexGrow: 1, p: isMobile ? 1.5 : 2 }}>
        <Typography
          variant={isMobile ? 'body2' : 'h6'}
          fontWeight="500"
          gutterBottom
          onClick={() => navigate(`/products/${product.id}`)}
          sx={{
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            display: '-webkit-box',
            WebkitLineClamp: 2,
            WebkitBoxOrient: 'vertical',
            minHeight: isMobile ? '2.5em' : '3em',
            cursor: 'pointer'
          }}
        >
          {product.name}
        </Typography>
        
        {product.category && (
          <Chip 
            label={product.category} 
            size="small" 
            sx={{ mb: 1 }}
          />
        )}
        
        <Stack direction="row" alignItems="center" justifyContent="space-between">
          <Box>
            <Typography 
              variant={isMobile ? 'h6' : 'h5'}
              color="primary"
              fontWeight="bold"
            >
              ${Number(product.sale_price || product.price).toFixed(2)}
            </Typography>
            {product.sale_price && product.sale_price < product.price && (
              <Typography
                variant="caption"
                color="text.secondary"
                sx={{ textDecoration: 'line-through' }}
              >
                ${Number(product.price).toFixed(2)}
              </Typography>
            )}
          </Box>
          
          <Box>
            {getCartQuantity(product.id) > 0 && (
              <Badge 
                badgeContent={getCartQuantity(product.id)} 
                color="primary"
                sx={{ mr: 1 }}
              >
                <span />
              </Badge>
            )}
            <IconButton
              color="primary"
              onClick={(e) => {
                e.stopPropagation();
                handleAddToCart(product);
              }}
              size={isMobile ? 'small' : 'medium'}
            >
              <AddIcon />
            </IconButton>
          </Box>
        </Stack>
      </CardContent>
    </Card>
  );
  
  return (
    <Box sx={{ pb: isMobile ? 8 : 2 }}>
      {/* Category Navigation */}
      <CategoryNav />
      
      <Container sx={{ mt: 3 }}>
        {/* Search Bar */}
        <Box sx={{ mb: 3 }}>
          <SearchBar 
            placeholder="Search products..." 
            variant="elevated"
          />
        </Box>
        
        {/* Breadcrumbs */}
        {category && (
          <Breadcrumbs sx={{ mb: 2 }}>
            <Link 
              underline="hover" 
              color="inherit" 
              href="#"
              onClick={(e) => {
                e.preventDefault();
                navigate('/');
              }}
            >
              Home
            </Link>
            {parentCategory && (
              <Link
                underline="hover"
                color="inherit"
                href="#"
                onClick={(e) => {
                  e.preventDefault();
                  navigate(`/products?category=${parentCategory.id}`);
                }}
              >
                {parentCategory.name}
              </Link>
            )}
            <Typography color="text.primary">{category.name}</Typography>
          </Breadcrumbs>
        )}
        
        {/* Header Controls */}
        <Box sx={{ mb: 3 }}>
          <Stack
            direction="row"
            justifyContent="space-between"
            alignItems="center"
            spacing={2}
          >
            <Typography variant={isMobile ? 'h5' : 'h4'} fontWeight="bold">
              {searchQuery ? (
                <>Search Results for: "{searchQuery}"</>
              ) : category ? (
                category.name
              ) : (
                'All Products'
              )}
            </Typography>
            
            <Stack direction="row" spacing={2} alignItems="center">
              <FormControl size="small" sx={{ minWidth: 120 }}>
                <InputLabel>Sort By</InputLabel>
                <Select
                  value={sortBy}
                  label="Sort By"
                  onChange={(e) => handleSortChange(e.target.value)}
                >
                  <MenuItem value="name">Name</MenuItem>
                  <MenuItem value="price_low">Price: Low to High</MenuItem>
                  <MenuItem value="price_high">Price: High to Low</MenuItem>
                </Select>
              </FormControl>
              
              {!isMobile && (
                <IconButton
                  onClick={() => dispatch(setViewMode(viewMode === 'grid' ? 'list' : 'grid'))}
                >
                  {viewMode === 'grid' ? <ListIcon /> : <GridIcon />}
                </IconButton>
              )}
            </Stack>
          </Stack>
        </Box>
        
        {/* Products Grid/List */}
        {loading ? (
          <Grid container spacing={2}>
            {[...Array(8)].map((_, i) => (
              <Grid 
                item 
                xs={viewMode === 'list' ? 12 : 6} 
                sm={viewMode === 'list' ? 12 : 4} 
                md={viewMode === 'list' ? 12 : 3} 
                key={i}
              >
                <Skeleton variant="rectangular" height={200} />
                <Box sx={{ pt: 0.5 }}>
                  <Skeleton />
                  <Skeleton width="60%" />
                </Box>
              </Grid>
            ))}
          </Grid>
        ) : products.length === 0 ? (
          <Box sx={{ textAlign: 'center', py: 8 }}>
            <Typography variant="h6" color="text.secondary">
              No products found in this category
            </Typography>
            <Button
              variant="contained"
              sx={{ mt: 2 }}
              onClick={() => navigate('/products')}
            >
              View All Products
            </Button>
          </Box>
        ) : (
          <AnimatePresence>
            <Grid container spacing={2}>
              {products.map((product, index) => (
                <Grid 
                  item 
                  xs={viewMode === 'list' ? 12 : 6} 
                  sm={viewMode === 'list' ? 12 : 4} 
                  md={viewMode === 'list' ? 12 : 3}
                  key={product.id}
                >
                  <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -20 }}
                    transition={{ delay: index * 0.05 }}
                  >
                    <ProductCard product={product} />
                  </motion.div>
                </Grid>
              ))}
            </Grid>
          </AnimatePresence>
        )}
      </Container>
    </Box>
  );
};

export default ProductsPage;