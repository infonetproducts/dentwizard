import React, { useState, createContext, useContext, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Link, useNavigate, useParams } from 'react-router-dom';
import { ThemeProvider, createTheme, CssBaseline } from '@mui/material';
import {
  AppBar, Toolbar, Typography, Button, Container, Grid, Card, CardMedia,
  CardContent, CardActions, Box, IconButton, Badge, Drawer, List, ListItem,
  ListItemText, ListItemIcon, Divider, TextField, Select, MenuItem, FormControl,
  InputLabel, Chip, Paper, Avatar, Tabs, Tab, Stack, Alert, Snackbar,
  useMediaQuery, useTheme as useMuiTheme, Dialog, DialogTitle, DialogContent,
  DialogActions, Stepper, Step, StepLabel, Fab, BottomNavigation, BottomNavigationAction
} from '@mui/material';
import {
  ShoppingCart, Menu as MenuIcon, Person, Home, Category, Search,
  Add, Remove, Delete, Close, LocalShipping, CheckCircle, ArrowBack,
  Favorite, FavoriteBorder, Star, Email, Phone, Business, ShoppingBag,
  LocationOn, Payment, Receipt
} from '@mui/icons-material';
import { mockProducts, mockCategories, mockUser, mockOrders } from './mockData';
import toast, { Toaster } from 'react-hot-toast';
import dentwizardLogo from './images/dentwizard.png';

// Create theme
const theme = createTheme({
  palette: {
    primary: {
      main: '#1976d2',
      light: '#42a5f5',
      dark: '#1565c0',
    },
    secondary: {
      main: '#ff9800',
    },
    background: {
      default: '#f5f5f5',
    },
  },
  typography: {
    h4: {
      fontWeight: 600,
    },
    h5: {
      fontWeight: 600,
    },
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          textTransform: 'none',
          borderRadius: 8,
        },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          borderRadius: 12,
          boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
        },
      },
    },
  },
});

// Cart Context
const CartContext = createContext();
const useCart = () => useContext(CartContext);

// Cart Provider Component
function CartProvider({ children }) {
  const [cart, setCart] = useState([]);
  const [cartOpen, setCartOpen] = useState(false);

  const addToCart = (product, size, color, quantity = 1) => {
    const existingItem = cart.find(
      item => item.id === product.id && item.size === size && item.color === color
    );

    if (existingItem) {
      setCart(cart.map(item =>
        item.id === product.id && item.size === size && item.color === color
          ? { ...item, quantity: item.quantity + quantity }
          : item
      ));
    } else {
      setCart([...cart, { ...product, size, color, quantity }]);
    }
    toast.success('Added to cart!');
  };

  const removeFromCart = (id, size, color) => {
    setCart(cart.filter(item => !(item.id === id && item.size === size && item.color === color)));
  };

  const updateQuantity = (id, size, color, quantity) => {
    if (quantity === 0) {
      removeFromCart(id, size, color);
    } else {
      setCart(cart.map(item =>
        item.id === id && item.size === size && item.color === color
          ? { ...item, quantity }
          : item
      ));
    }
  };

  const clearCart = () => setCart([]);

  const getTotal = () => cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

  return (
    <CartContext.Provider value={{
      cart,
      cartOpen,
      setCartOpen,
      addToCart,
      removeFromCart,
      updateQuantity,
      clearCart,
      getTotal
    }}>
      {children}
    </CartContext.Provider>
  );
}

// Navigation Component
function Navigation() {
  const muiTheme = useMuiTheme();
  const isMobile = useMediaQuery(muiTheme.breakpoints.down('md'));
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { cart, setCartOpen } = useCart();
  const navigate = useNavigate();

  const cartItemCount = cart.reduce((sum, item) => sum + item.quantity, 0);

  return (
    <>
      <AppBar position="sticky" elevation={0} sx={{ bgcolor: 'white', borderBottom: '1px solid #e0e0e0' }}>
        <Toolbar sx={{ minHeight: { xs: 64, md: 70 } }}>
          {isMobile && (
            <IconButton edge="start" onClick={() => setMobileMenuOpen(true)}>
              <MenuIcon />
            </IconButton>
          )}
          
          <Box
            sx={{ 
              flexGrow: 1, 
              display: 'flex',
              alignItems: 'center',
              cursor: 'pointer'
            }}
            onClick={() => navigate('/')}
          >
            <img 
              src={dentwizardLogo} 
              alt="DentWizard" 
              style={{ 
                height: isMobile ? '40px' : '50px',
                width: 'auto'
              }} 
            />
          </Box>

          {!isMobile && (
            <Box sx={{ display: 'flex', gap: 2, mr: 2 }}>
              <Button color="primary" onClick={() => navigate('/')}>Home</Button>
              <Button color="primary" onClick={() => navigate('/products')}>Products</Button>
              <Button color="primary" onClick={() => navigate('/profile')}>Profile</Button>
            </Box>
          )}

          <IconButton onClick={() => setCartOpen(true)} color="primary">
            <Badge badgeContent={cartItemCount} color="secondary">
              <ShoppingCart />
            </Badge>
          </IconButton>

          {!isMobile && (
            <IconButton onClick={() => navigate('/profile')} color="primary">
              <Person />
            </IconButton>
          )}
        </Toolbar>
      </AppBar>

      {/* Mobile Menu Drawer */}
      <Drawer
        anchor="left"
        open={mobileMenuOpen}
        onClose={() => setMobileMenuOpen(false)}
      >
        <Box sx={{ width: 250, pt: 2 }}>
          <Box sx={{ px: 2, mb: 2, display: 'flex', alignItems: 'center' }}>
            <img 
              src={dentwizardLogo} 
              alt="DentWizard" 
              style={{ 
                height: '40px',
                width: 'auto'
              }} 
            />
          </Box>
          <Divider />
          <List>
            <ListItem button onClick={() => { navigate('/'); setMobileMenuOpen(false); }}>
              <ListItemIcon><Home /></ListItemIcon>
              <ListItemText primary="Home" />
            </ListItem>
            <ListItem button onClick={() => { navigate('/products'); setMobileMenuOpen(false); }}>
              <ListItemIcon><Category /></ListItemIcon>
              <ListItemText primary="Products" />
            </ListItem>
            <ListItem button onClick={() => { navigate('/profile'); setMobileMenuOpen(false); }}>
              <ListItemIcon><Person /></ListItemIcon>
              <ListItemText primary="Profile" />
            </ListItem>
          </List>
        </Box>
      </Drawer>
    </>
  );
}

// Cart Drawer Component
function CartDrawer() {
  const { cart, cartOpen, setCartOpen, removeFromCart, updateQuantity, getTotal } = useCart();
  const navigate = useNavigate();

  return (
    <Drawer
      anchor="right"
      open={cartOpen}
      onClose={() => setCartOpen(false)}
      sx={{ '& .MuiDrawer-paper': { width: { xs: '100%', sm: 400 } } }}
    >
      <Box sx={{ p: 2 }}>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
          <Typography variant="h6">Shopping Cart</Typography>
          <IconButton onClick={() => setCartOpen(false)}>
            <Close />
          </IconButton>
        </Box>
        
        <Divider />
        
        {cart.length === 0 ? (
          <Box sx={{ textAlign: 'center', py: 4 }}>
            <ShoppingCart sx={{ fontSize: 64, color: 'text.secondary', mb: 2 }} />
            <Typography color="text.secondary">Your cart is empty</Typography>
            <Button
              variant="contained"
              sx={{ mt: 2 }}
              onClick={() => {
                setCartOpen(false);
                navigate('/products');
              }}
            >
              Start Shopping
            </Button>
          </Box>
        ) : (
          <>
            <List sx={{ flexGrow: 1, overflow: 'auto', maxHeight: 'calc(100vh - 250px)' }}>
              {cart.map((item, index) => (
                <ListItem key={index} sx={{ flexDirection: 'column', alignItems: 'stretch', mb: 2 }}>
                  <Box sx={{ display: 'flex', gap: 2 }}>
                    <Box
                      sx={{
                        width: 80,
                        height: 80,
                        bgcolor: 'grey.200',
                        borderRadius: 1,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                      }}
                    >
                      <img src={item.image} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: 4 }} />
                    </Box>
                    <Box sx={{ flex: 1 }}>
                      <Typography variant="body1" fontWeight={500}>{item.name}</Typography>
                      <Typography variant="body2" color="text.secondary">
                        {item.size} / {item.color}
                      </Typography>
                      <Typography variant="body1" color="primary" fontWeight={600}>
                        ${item.price}
                      </Typography>
                    </Box>
                  </Box>
                  <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mt: 1 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                      <IconButton
                        size="small"
                        onClick={() => updateQuantity(item.id, item.size, item.color, item.quantity - 1)}
                      >
                        <Remove />
                      </IconButton>
                      <Typography sx={{ mx: 2 }}>{item.quantity}</Typography>
                      <IconButton
                        size="small"
                        onClick={() => updateQuantity(item.id, item.size, item.color, item.quantity + 1)}
                      >
                        <Add />
                      </IconButton>
                    </Box>
                    <IconButton
                      color="error"
                      onClick={() => removeFromCart(item.id, item.size, item.color)}
                    >
                      <Delete />
                    </IconButton>
                  </Box>
                </ListItem>
              ))}
            </List>
            
            <Divider sx={{ my: 2 }} />
            
            <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
              <Typography variant="h6">Total:</Typography>
              <Typography variant="h6" color="primary">${getTotal().toFixed(2)}</Typography>
            </Box>
            
            <Button
              fullWidth
              variant="contained"
              size="large"
              startIcon={<ShoppingCart />}
              onClick={() => {
                setCartOpen(false);
                navigate('/checkout');
              }}
            >
              Proceed to Checkout
            </Button>
          </>
        )}
      </Box>
    </Drawer>
  );
}

// Home Page Component
function HomePage() {
  const navigate = useNavigate();
  const featuredProducts = mockProducts.filter(p => p.featured).slice(0, 3);

  return (
    <Container sx={{ py: 4 }}>
      {/* Hero Section */}
      <Paper
        sx={{
          p: 6,
          mb: 4,
          background: 'linear-gradient(135deg, #1976d2 0%, #42a5f5 100%)',
          color: 'white',
          borderRadius: 3,
          textAlign: 'center'
        }}
      >
        <Typography variant="h4" gutterBottom fontWeight={700}>
          Corporate Apparel & Merchandise
        </Typography>
        <Typography variant="h6" sx={{ mb: 3, opacity: 0.9 }}>
          Premium Corporate Apparel & Merchandise
        </Typography>
        <Typography variant="body1" sx={{ mb: 4, maxWidth: 600, mx: 'auto' }}>
          Shop our exclusive collection of professional apparel designed for the DentWizard team.
          Quality products that represent our brand with pride.
        </Typography>
        <Button
          variant="contained"
          size="large"
          color="secondary"
          onClick={() => navigate('/products')}
          sx={{ mr: 2 }}
        >
          Shop Now
        </Button>
        <Button
          variant="outlined"
          size="large"
          sx={{ color: 'white', borderColor: 'white' }}
          onClick={() => navigate('/profile')}
        >
          View Profile
        </Button>
      </Paper>

      {/* Budget Status */}
      <Paper sx={{ p: 3, mb: 4, bgcolor: 'primary.50' }}>
        <Grid container spacing={3}>
          <Grid item xs={12} md={4}>
            <Box sx={{ textAlign: 'center' }}>
              <Typography variant="h4" color="primary">${mockUser.budget.allocated}</Typography>
              <Typography variant="body2" color="text.secondary">Total Budget</Typography>
            </Box>
          </Grid>
          <Grid item xs={12} md={4}>
            <Box sx={{ textAlign: 'center' }}>
              <Typography variant="h4" color="warning.main">${mockUser.budget.used}</Typography>
              <Typography variant="body2" color="text.secondary">Used</Typography>
            </Box>
          </Grid>
          <Grid item xs={12} md={4}>
            <Box sx={{ textAlign: 'center' }}>
              <Typography variant="h4" color="success.main">${mockUser.budget.remaining}</Typography>
              <Typography variant="body2" color="text.secondary">Remaining</Typography>
            </Box>
          </Grid>
        </Grid>
      </Paper>

      {/* Featured Products */}
      <Typography variant="h4" gutterBottom sx={{ mb: 3 }}>
        Featured Products
      </Typography>
      <Grid container spacing={3}>
        {featuredProducts.map(product => (
          <Grid item xs={12} md={4} key={product.id}>
            <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
              {product.badge && (
                <Chip
                  label={product.badge}
                  color="secondary"
                  size="small"
                  sx={{ position: 'absolute', top: 16, right: 16, zIndex: 1 }}
                />
              )}
              <CardMedia
                component="img"
                height="250"
                image={product.image}
                alt={product.name}
              />
              <CardContent sx={{ flexGrow: 1 }}>
                <Typography variant="h6" gutterBottom>
                  {product.name}
                </Typography>
                <Typography variant="body2" color="text.secondary" paragraph>
                  {product.description}
                </Typography>
                <Typography variant="h5" color="primary">
                  ${product.price}
                </Typography>
              </CardContent>
              <CardActions>
                <Button
                  fullWidth
                  variant="contained"
                  onClick={() => navigate(`/products/${product.id}`)}
                >
                  View Details
                </Button>
              </CardActions>
            </Card>
          </Grid>
        ))}
      </Grid>

      {/* Categories */}
      <Typography variant="h4" gutterBottom sx={{ mt: 6, mb: 3 }}>
        Shop by Category
      </Typography>
      <Grid container spacing={2}>
        {mockCategories.slice(1, 7).map(category => (
          <Grid item xs={6} md={4} lg={2} key={category.name}>
            <Paper
              sx={{
                p: 3,
                textAlign: 'center',
                cursor: 'pointer',
                transition: 'all 0.3s',
                '&:hover': {
                  transform: 'translateY(-4px)',
                  boxShadow: 3
                }
              }}
              onClick={() => navigate('/products')}
            >
              <Category sx={{ fontSize: 40, color: 'primary.main', mb: 1 }} />
              <Typography variant="body1" fontWeight={500}>
                {category.name}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                {category.count} items
              </Typography>
            </Paper>
          </Grid>
        ))}
      </Grid>
    </Container>
  );
}

// Products Page Component  
function ProductsPage() {
  const [selectedCategory, setSelectedCategory] = useState('All Products');
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState('name');
  const navigate = useNavigate();

  const filteredProducts = mockProducts
    .filter(product => 
      (selectedCategory === 'All Products' || product.category === selectedCategory) &&
      product.name.toLowerCase().includes(searchTerm.toLowerCase())
    )
    .sort((a, b) => {
      if (sortBy === 'name') return a.name.localeCompare(b.name);
      if (sortBy === 'price-low') return a.price - b.price;
      if (sortBy === 'price-high') return b.price - a.price;
      return 0;
    });

  return (
    <Container sx={{ py: 4 }}>
      <Typography variant="h4" gutterBottom>
        Products
      </Typography>

      {/* Filters */}
      <Grid container spacing={2} sx={{ mb: 4 }}>
        <Grid item xs={12} md={4}>
          <TextField
            fullWidth
            placeholder="Search products..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            InputProps={{
              startAdornment: <Search sx={{ mr: 1, color: 'text.secondary' }} />
            }}
          />
        </Grid>
        <Grid item xs={12} md={4}>
          <FormControl fullWidth>
            <InputLabel>Category</InputLabel>
            <Select
              value={selectedCategory}
              label="Category"
              onChange={(e) => setSelectedCategory(e.target.value)}
            >
              {mockCategories.map(cat => (
                <MenuItem key={cat.name} value={cat.name}>
                  {cat.name} ({cat.count})
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={12} md={4}>
          <FormControl fullWidth>
            <InputLabel>Sort By</InputLabel>
            <Select
              value={sortBy}
              label="Sort By"
              onChange={(e) => setSortBy(e.target.value)}
            >
              <MenuItem value="name">Name</MenuItem>
              <MenuItem value="price-low">Price: Low to High</MenuItem>
              <MenuItem value="price-high">Price: High to Low</MenuItem>
            </Select>
          </FormControl>
        </Grid>
      </Grid>

      {/* Products Grid */}
      <Grid container spacing={3}>
        {filteredProducts.map(product => (
          <Grid item xs={12} sm={6} md={4} lg={3} key={product.id}>
            <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
              {product.badge && (
                <Chip
                  label={product.badge}
                  color="secondary"
                  size="small"
                  sx={{ position: 'absolute', top: 8, right: 8, zIndex: 1 }}
                />
              )}
              <CardMedia
                component="img"
                height="200"
                image={product.image}
                alt={product.name}
                sx={{ cursor: 'pointer' }}
                onClick={() => navigate(`/products/${product.id}`)}
              />
              <CardContent sx={{ flexGrow: 1 }}>
                <Typography variant="body1" fontWeight={500} gutterBottom>
                  {product.name}
                </Typography>
                <Typography variant="h6" color="primary">
                  ${product.price}
                </Typography>
                <Box sx={{ mt: 1 }}>
                  {product.colors.slice(0, 4).map(color => (
                    <Chip
                      key={color}
                      label={color}
                      size="small"
                      sx={{ mr: 0.5, mb: 0.5 }}
                      variant="outlined"
                    />
                  ))}
                </Box>
              </CardContent>
              <CardActions>
                <Button
                  fullWidth
                  variant="contained"
                  onClick={() => navigate(`/products/${product.id}`)}
                >
                  View Details
                </Button>
              </CardActions>
            </Card>
          </Grid>
        ))}
      </Grid>
    </Container>
  );
}

// Product Detail Page Component
function ProductDetailPage() {
  const { id } = useParams();
  const product = mockProducts.find(p => p.id === parseInt(id));
  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [quantity, setQuantity] = useState(1);
  const { addToCart } = useCart();
  const navigate = useNavigate();

  useEffect(() => {
    if (product) {
      setSelectedSize(product.sizes[0]);
      setSelectedColor(product.colors[0]);
    }
  }, [product]);

  if (!product) {
    return (
      <Container sx={{ py: 4, textAlign: 'center' }}>
        <Typography variant="h5">Product not found</Typography>
        <Button onClick={() => navigate('/products')} sx={{ mt: 2 }}>
          Back to Products
        </Button>
      </Container>
    );
  }

  const handleAddToCart = () => {
    addToCart(product, selectedSize, selectedColor, quantity);
  };

  return (
    <Container sx={{ py: 4 }}>
      <Button
        startIcon={<ArrowBack />}
        onClick={() => navigate('/products')}
        sx={{ mb: 2 }}
      >
        Back to Products
      </Button>

      <Grid container spacing={4}>
        <Grid item xs={12} md={6}>
          <Paper sx={{ p: 2 }}>
            <img
              src={product.image}
              alt={product.name}
              style={{ width: '100%', height: 'auto', borderRadius: 8 }}
            />
          </Paper>
        </Grid>

        <Grid item xs={12} md={6}>
          <Typography variant="h4" gutterBottom>
            {product.name}
          </Typography>
          
          {product.badge && (
            <Chip label={product.badge} color="secondary" sx={{ mb: 2 }} />
          )}

          <Typography variant="h3" color="primary" gutterBottom>
            ${product.price}
          </Typography>

          <Typography variant="body1" paragraph color="text.secondary">
            {product.description}
          </Typography>

          <Box sx={{ mb: 3 }}>
            <Typography variant="subtitle1" gutterBottom fontWeight={500}>
              Color
            </Typography>
            <Box sx={{ display: 'flex', gap: 1 }}>
              {product.colors.map(color => (
                <Chip
                  key={color}
                  label={color}
                  onClick={() => setSelectedColor(color)}
                  color={selectedColor === color ? 'primary' : 'default'}
                  variant={selectedColor === color ? 'filled' : 'outlined'}
                />
              ))}
            </Box>
          </Box>

          <Box sx={{ mb: 3 }}>
            <Typography variant="subtitle1" gutterBottom fontWeight={500}>
              Size
            </Typography>
            <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>
              {product.sizes.map(size => (
                <Chip
                  key={size}
                  label={size}
                  onClick={() => setSelectedSize(size)}
                  color={selectedSize === size ? 'primary' : 'default'}
                  variant={selectedSize === size ? 'filled' : 'outlined'}
                />
              ))}
            </Box>
          </Box>

          <Box sx={{ mb: 3 }}>
            <Typography variant="subtitle1" gutterBottom fontWeight={500}>
              Quantity
            </Typography>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <IconButton
                onClick={() => setQuantity(Math.max(1, quantity - 1))}
                disabled={quantity <= 1}
              >
                <Remove />
              </IconButton>
              <Typography sx={{ mx: 2, minWidth: 40, textAlign: 'center' }}>
                {quantity}
              </Typography>
              <IconButton onClick={() => setQuantity(quantity + 1)}>
                <Add />
              </IconButton>
            </Box>
          </Box>

          <Box sx={{ display: 'flex', gap: 2 }}>
            <Button
              variant="contained"
              size="large"
              fullWidth
              startIcon={<ShoppingCart />}
              onClick={handleAddToCart}
            >
              Add to Cart
            </Button>
            <IconButton size="large" sx={{ border: '1px solid', borderColor: 'divider' }}>
              <FavoriteBorder />
            </IconButton>
          </Box>

          <Divider sx={{ my: 3 }} />

          <Box>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
              <LocalShipping sx={{ mr: 1 }} />
              <Typography variant="body2">Free shipping on orders over $100</Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <CheckCircle sx={{ mr: 1, color: 'success.main' }} />
              <Typography variant="body2">In Stock - Ships within 2-3 business days</Typography>
            </Box>
          </Box>
        </Grid>
      </Grid>
    </Container>
  );
}

// Profile Page Component
function ProfilePage() {
  const [tabValue, setTabValue] = useState(0);

  return (
    <Container sx={{ py: 4 }}>
      <Paper sx={{
        p: 3,
        mb: 3,
        background: 'linear-gradient(135deg, #1976d2 0%, #42a5f5 100%)',
        color: 'white'
      }}>
        <Grid container spacing={2} alignItems="center">
          <Grid item>
            <Avatar sx={{ width: 80, height: 80, bgcolor: 'white', color: 'primary.main' }}>
              {mockUser.firstName[0]}{mockUser.lastName[0]}
            </Avatar>
          </Grid>
          <Grid item xs>
            <Typography variant="h4" gutterBottom>
              {mockUser.firstName} {mockUser.lastName}
            </Typography>
            <Stack direction="row" spacing={2}>
              <Chip
                icon={<Email />}
                label={mockUser.email}
                sx={{ bgcolor: 'rgba(255,255,255,0.2)', color: 'white' }}
              />
              <Chip
                icon={<Business />}
                label={mockUser.department}
                sx={{ bgcolor: 'rgba(255,255,255,0.2)', color: 'white' }}
              />
            </Stack>
          </Grid>
        </Grid>
      </Paper>

      <Paper>
        <Tabs value={tabValue} onChange={(e, v) => setTabValue(v)}>
          <Tab label="Profile Info" />
          <Tab label="Order History" />
          <Tab label="Settings" />
        </Tabs>

        <Box sx={{ p: 3 }}>
          {tabValue === 0 && (
            <Grid container spacing={3}>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="First Name"
                  value={mockUser.firstName}
                  disabled
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Last Name"
                  value={mockUser.lastName}
                  disabled
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Email"
                  value={mockUser.email}
                  disabled
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Employee ID"
                  value={mockUser.employeeId}
                  disabled
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Department"
                  value={mockUser.department}
                  disabled
                />
              </Grid>
            </Grid>
          )}

          {tabValue === 1 && (
            <Stack spacing={2}>
              {mockOrders.map(order => (
                <Card key={order.id}>
                  <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
                      <Box>
                        <Typography variant="h6">{order.id}</Typography>
                        <Typography variant="body2" color="text.secondary">
                          {order.date}
                        </Typography>
                      </Box>
                      <Chip
                        label={order.status.toUpperCase()}
                        color={order.status === 'delivered' ? 'success' : 'primary'}
                        size="small"
                      />
                    </Box>
                    <Divider sx={{ my: 1 }} />
                    {order.items.map((item, idx) => (
                      <Typography key={idx} variant="body2">
                        {item.quantity}x {item.name} - ${item.price}
                      </Typography>
                    ))}
                    <Box sx={{ mt: 2, display: 'flex', justifyContent: 'space-between' }}>
                      <Typography variant="h6">Total: ${order.total}</Typography>
                      <Button size="small">View Details</Button>
                    </Box>
                  </CardContent>
                </Card>
              ))}
            </Stack>
          )}

          {tabValue === 2 && (
            <Typography>Settings and preferences would go here</Typography>
          )}
        </Box>
      </Paper>
    </Container>
  );
}

// Checkout Page Component
function CheckoutPage() {
  const { cart, getTotal, clearCart } = useCart();
  const navigate = useNavigate();
  const [activeStep, setActiveStep] = useState(0);

  const handlePlaceOrder = () => {
    toast.success('Order placed successfully!');
    clearCart();
    setTimeout(() => navigate('/'), 2000);
  };

  if (cart.length === 0) {
    return (
      <Container sx={{ py: 4, textAlign: 'center' }}>
        <CheckCircle sx={{ fontSize: 64, color: 'success.main', mb: 2 }} />
        <Typography variant="h4" gutterBottom>
          Order Placed Successfully!
        </Typography>
        <Button variant="contained" onClick={() => navigate('/')} sx={{ mt: 2 }}>
          Continue Shopping
        </Button>
      </Container>
    );
  }

  return (
    <Container sx={{ py: 4 }}>
      <Typography variant="h4" gutterBottom>
        Checkout
      </Typography>

      <Stepper activeStep={activeStep} sx={{ mb: 4 }}>
        <Step><StepLabel>Shipping</StepLabel></Step>
        <Step><StepLabel>Payment</StepLabel></Step>
        <Step><StepLabel>Review</StepLabel></Step>
      </Stepper>

      <Grid container spacing={3}>
        <Grid item xs={12} md={8}>
          <Paper sx={{ p: 3 }}>
            {activeStep === 0 && (
              <>
                <Typography variant="h6" gutterBottom>Shipping Information</Typography>
                <Grid container spacing={2}>
                  <Grid item xs={12} sm={6}>
                    <TextField fullWidth label="First Name" defaultValue={mockUser.firstName} />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField fullWidth label="Last Name" defaultValue={mockUser.lastName} />
                  </Grid>
                  <Grid item xs={12}>
                    <TextField fullWidth label="Address" />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField fullWidth label="City" />
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <TextField fullWidth label="ZIP Code" />
                  </Grid>
                </Grid>
              </>
            )}

            {activeStep === 1 && (
              <>
                <Typography variant="h6" gutterBottom>Payment Method</Typography>
                <Typography variant="body1">Purchase Order / Company Account</Typography>
              </>
            )}

            {activeStep === 2 && (
              <>
                <Typography variant="h6" gutterBottom>Review Your Order</Typography>
                <List>
                  {cart.map((item, index) => (
                    <ListItem key={index}>
                      <ListItemText
                        primary={item.name}
                        secondary={`${item.size} / ${item.color} - Qty: ${item.quantity}`}
                      />
                      <Typography>${(item.price * item.quantity).toFixed(2)}</Typography>
                    </ListItem>
                  ))}
                </List>
              </>
            )}

            <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 3 }}>
              <Button
                disabled={activeStep === 0}
                onClick={() => setActiveStep(activeStep - 1)}
              >
                Back
              </Button>
              <Button
                variant="contained"
                onClick={() => {
                  if (activeStep === 2) {
                    handlePlaceOrder();
                  } else {
                    setActiveStep(activeStep + 1);
                  }
                }}
              >
                {activeStep === 2 ? 'Place Order' : 'Next'}
              </Button>
            </Box>
          </Paper>
        </Grid>

        <Grid item xs={12} md={4}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" gutterBottom>Order Summary</Typography>
            <List dense>
              {cart.map((item, index) => (
                <ListItem key={index}>
                  <ListItemText primary={`${item.quantity}x ${item.name}`} />
                  <Typography variant="body2">${(item.price * item.quantity).toFixed(2)}</Typography>
                </ListItem>
              ))}
            </List>
            <Divider sx={{ my: 2 }} />
            <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
              <Typography variant="h6">Total:</Typography>
              <Typography variant="h6" color="primary">${getTotal().toFixed(2)}</Typography>
            </Box>
          </Paper>
        </Grid>
      </Grid>
    </Container>
  );
}

// Bottom Navigation for Mobile
function MobileBottomNav() {
  const navigate = useNavigate();
  const [value, setValue] = useState(0);
  const muiTheme = useMuiTheme();
  const isMobile = useMediaQuery(muiTheme.breakpoints.down('md'));

  if (!isMobile) return null;

  return (
    <Paper sx={{ position: 'fixed', bottom: 0, left: 0, right: 0, zIndex: 1000 }} elevation={3}>
      <BottomNavigation
        value={value}
        onChange={(event, newValue) => setValue(newValue)}
      >
        <BottomNavigationAction
          label="Home"
          icon={<Home />}
          onClick={() => navigate('/')}
        />
        <BottomNavigationAction
          label="Products"
          icon={<Category />}
          onClick={() => navigate('/products')}
        />
        <BottomNavigationAction
          label="Profile"
          icon={<Person />}
          onClick={() => navigate('/profile')}
        />
      </BottomNavigation>
    </Paper>
  );
}

// Main App Component
function App() {
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <Router>
        <CartProvider>
          <Toaster position="top-center" />
          <Navigation />
          <CartDrawer />
          
          <Box sx={{ minHeight: '100vh', bgcolor: 'background.default', pb: { xs: 8, md: 0 } }}>
            <Routes>
              <Route path="/" element={<HomePage />} />
              <Route path="/products" element={<ProductsPage />} />
              <Route path="/products/:id" element={<ProductDetailPage />} />
              <Route path="/profile" element={<ProfilePage />} />
              <Route path="/checkout" element={<CheckoutPage />} />
            </Routes>
          </Box>
          
          <MobileBottomNav />
        </CartProvider>
      </Router>
    </ThemeProvider>
  );
}

export default App;