import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSelector, useDispatch } from 'react-redux';
import {
  Box,
  Grid,
  Card,
  CardMedia,
  CardContent,
  Typography,
  Button,
  Container,
  Paper,
  Stack,
  Skeleton,
  useTheme,
  useMediaQuery
} from '@mui/material';
import {
  ArrowForward as ArrowIcon
} from '@mui/icons-material';
import { motion } from 'framer-motion';
import { fetchProducts, fetchCategories } from '../store/slices/productsSlice';
import CategoryNav from '../components/CategoryNav';
import SearchBar from '../components/SearchBar';

const HomePage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const isTablet = useMediaQuery(theme.breakpoints.down('md'));
  
  const { items: products, loading: productsLoading } = useSelector(state => state.products);
  
  useEffect(() => {
    dispatch(fetchProducts({ limit: 8 }));
    dispatch(fetchCategories());
  }, [dispatch]);
  
  return (
    <Box sx={{ minHeight: '100vh', pb: 4 }}>
      {/* Hero Section */}
      <Paper
        elevation={0}
        sx={{
          background: 'linear-gradient(135deg, #134a91 0%, #1e6bb8 100%)',
          color: 'white',
          py: isMobile ? 4 : 6,
          mb: 3,
          borderRadius: 2,
          mx: 2
        }}
      >
        <Container>
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            style={{ textAlign: 'center' }}
          >
            <Typography
              variant={isMobile ? 'h4' : 'h3'}
              fontWeight="bold"
              gutterBottom
              align="center"
            >
              DentWizard Apparel Store
            </Typography>
            <Typography 
              variant={isMobile ? 'body1' : 'h6'} 
              sx={{ mb: 3, opacity: 0.95 }}
              align="center"
            >
              Premium branded apparel and promotional products for your team
            </Typography>
            
            {/* Search Bar */}
            <Box sx={{ mb: 3 }}>
              <SearchBar 
                placeholder="Search for products, categories, or brands..." 
                variant="hero"
              />
            </Box>
            
            <Box sx={{ display: 'flex', justifyContent: 'center' }}>
              <Button
                variant="contained"
                size="large"
                onClick={() => navigate('/products')}
                sx={{
                  bgcolor: 'white',
                  color: 'primary.main',
                  '&:hover': { bgcolor: 'grey.100' }
                }}
              >
                Shop Now
              </Button>
            </Box>
          </motion.div>
        </Container>
      </Paper>
      
      {/* Category Navigation Bar */}
      <CategoryNav />
      
      <Container sx={{ mt: 4 }}>
        {/* Featured Products Section - Full Width */}
        <Stack direction="row" justifyContent="space-between" alignItems="center" mb={3}>
          <Typography variant={isMobile ? 'h5' : 'h4'} fontWeight="bold">
            Featured Products
          </Typography>
          <Button
            endIcon={<ArrowIcon />}
            onClick={() => navigate('/products')}
            variant="outlined"
          >
            View All
          </Button>
        </Stack>
        
        {productsLoading ? (
          <Grid container spacing={isMobile ? 2 : 3}>
            {[1, 2, 3, 4, 5, 6, 7, 8].map(i => (
              <Grid item xs={6} sm={4} md={3} key={i}>
                <Skeleton variant="rectangular" height={200} />
                <Skeleton variant="text" sx={{ mt: 1 }} />
                <Skeleton variant="text" width="60%" />
              </Grid>
            ))}
          </Grid>
        ) : (
          <Grid container spacing={isMobile ? 2 : 3}>
            {products.slice(0, 8).map((product, index) => (
              <Grid item xs={6} sm={4} md={3} key={product.id}>
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.05 }}
                >
                  <Card
                    sx={{
                      height: '100%',
                      display: 'flex',
                      flexDirection: 'column',
                      cursor: 'pointer',
                      '&:hover': {
                        transform: 'translateY(-4px)',
                        boxShadow: 4
                      },
                      transition: 'all 0.3s'
                    }}
                    onClick={() => navigate(`/products/${product.id}`)}
                  >
                    <CardMedia
                      component="img"
                      height={isMobile ? 180 : 260}
                      image={product.image_url || '/placeholder.png'}
                      alt={product.name}
                      sx={{ objectFit: 'cover' }}
                    />
                    <CardContent sx={{ flexGrow: 1, p: isMobile ? 1.5 : 2 }}>
                      <Typography
                        gutterBottom
                        variant={isMobile ? 'body2' : 'h6'}
                        sx={{ 
                          fontWeight: 500,
                          overflow: 'hidden',
                          textOverflow: 'ellipsis',
                          display: '-webkit-box',
                          WebkitLineClamp: 2,
                          WebkitBoxOrient: 'vertical',
                          minHeight: isMobile ? '2.5em' : '3em'
                        }}
                      >
                        {product.name}
                      </Typography>
                      <Typography
                        variant={isMobile ? 'h6' : 'h5'}
                        color="primary"
                        fontWeight="bold"
                        gutterBottom
                      >
                        ${Number(product.price).toFixed(2)}
                      </Typography>
                    </CardContent>
                  </Card>
                </motion.div>
              </Grid>
            ))}
          </Grid>
        )}
        
        {/* Promotional Section */}
        <Grid container spacing={3} sx={{ mt: 6 }}>
          <Grid item xs={12} md={6}>
            <Paper
              elevation={2}
              sx={{
                p: 4,
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                color: 'white',
                height: '100%',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center'
              }}
            >
              <Typography variant="h5" fontWeight="bold" gutterBottom>
                New Arrivals
              </Typography>
              <Typography variant="body1" sx={{ mb: 2, opacity: 0.9 }}>
                Check out our latest professional gear and equipment
              </Typography>
              <Button
                variant="contained"
                sx={{
                  bgcolor: 'white',
                  color: 'primary.main',
                  '&:hover': { bgcolor: 'grey.100' },
                  alignSelf: 'flex-start'
                }}
                onClick={() => navigate('/products?sort=newest')}
              >
                Shop New Items
              </Button>
            </Paper>
          </Grid>
          
          <Grid item xs={12} md={6}>
            <Paper
              elevation={2}
              sx={{
                p: 4,
                background: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                color: 'white',
                height: '100%',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center'
              }}
            >
              <Typography variant="h5" fontWeight="bold" gutterBottom>
                Special Offers
              </Typography>
              <Typography variant="body1" sx={{ mb: 2, opacity: 0.9 }}>
                Save on bulk orders and seasonal promotions
              </Typography>
              <Button
                variant="contained"
                sx={{
                  bgcolor: 'white',
                  color: '#f5576c',
                  '&:hover': { bgcolor: 'grey.100' },
                  alignSelf: 'flex-start'
                }}
                onClick={() => navigate('/products?category=promotional')}
              >
                View Deals
              </Button>
            </Paper>
          </Grid>
        </Grid>
      </Container>
    </Box>
  );
};

export default HomePage;