import React from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import dentwizardLogo from '../../images/dentwizard.png';
import {
  Box,
  AppBar,
  Toolbar,
  IconButton,
  Typography,
  Badge,
  Button,
  useTheme,
  useMediaQuery,
  Container,
  BottomNavigation,
  BottomNavigationAction,
  Paper
} from '@mui/material';
import {
  Menu as MenuIcon,
  ShoppingCart as ShoppingCartIcon,
  Person as PersonIcon,
  Home as HomeIcon,
  AccountCircle as AccountCircleIcon
} from '@mui/icons-material';
import { useMsal } from '@azure/msal-react';
import { toggleMobileMenu } from '../../store/slices/uiSlice';
import { fetchCart } from '../../store/slices/cartSlice';
import { fetchUserProfile } from '../../store/slices/profileSlice';
import CartDrawer from './CartDrawer';
import MobileSearch from './MobileSearch';
import MobileDrawer from './MobileDrawer';
import Footer from './Footer';
const Layout = () => {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useDispatch();
  const { instance } = useMsal();
  
  const { summary, budget } = useSelector(state => state.cart);
  const { user } = useSelector(state => state.auth);
  const { budget: profileBudget, user: profileUser } = useSelector(state => state.profile);
  const cartItemCount = summary?.total_items || 0;
  const cartTotal = summary?.total || 0;
  const userBudget = profileBudget?.budget_balance || 0;

  const [bottomNavValue, setBottomNavValue] = React.useState(0);

  // Fetch cart and user profile on component mount
  React.useEffect(() => {
    dispatch(fetchCart());
    dispatch(fetchUserProfile());
  }, [dispatch]);

  React.useEffect(() => {
    // Update bottom navigation based on current route
    const path = location.pathname;
    if (path === '/') setBottomNavValue(0);
    else if (path === '/cart') setBottomNavValue(1);
    else if (path === '/profile') setBottomNavValue(2);
  }, [location]);

  const menuItems = [
    { text: 'Home', icon: <HomeIcon />, path: '/' },
    { text: 'Cart', icon: <ShoppingCartIcon />, path: '/cart' },
    { text: 'Profile', icon: <PersonIcon />, path: '/profile' },
  ];
  
  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
      {/* Header */}
      <AppBar position="fixed" elevation={0} sx={{ bgcolor: 'background.paper', borderBottom: 1, borderColor: 'divider' }}>
        <Toolbar sx={{ minHeight: { xs: 64, md: 70 } }}>
          {isMobile && (
            <IconButton
              edge="start"
              onClick={() => dispatch(toggleMobileMenu())}
              sx={{ mr: 2, color: 'text.primary' }}
            >
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
            <Box sx={{ display: 'flex', gap: 2, mx: 4 }}>
              <Button 
                onClick={() => navigate('/')} 
                sx={{ 
                  color: location.pathname === '/' ? 'primary.main' : 'text.primary',
                  fontWeight: location.pathname === '/' ? 700 : 600,
                  '&:hover': {
                    bgcolor: 'rgba(19, 74, 145, 0.08)',
                    textDecoration: 'underline'
                  }
                }}
              >
                Home
              </Button>
              <Button 
                onClick={() => navigate('/products')} 
                sx={{ 
                  color: location.pathname === '/products' ? 'primary.main' : 'text.primary',
                  fontWeight: location.pathname === '/products' ? 700 : 600,
                  '&:hover': {
                    bgcolor: 'rgba(19, 74, 145, 0.08)',
                    textDecoration: 'underline'
                  }
                }}
              >
                Products
              </Button>
            </Box>
          )}

          {isMobile && <MobileSearch />}

          {/* Budget and Cart Info */}
          {!isMobile ? (
            // Desktop version
            <Box sx={{ 
              display: 'flex', 
              flexDirection: 'column', 
              alignItems: 'flex-end',
              mr: 2
            }}>
              <Typography variant="caption" color="text.secondary">
                {profileUser?.name || user?.name || 'Guest'}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Budget Balance: <strong>${userBudget.toFixed(2)}</strong>
              </Typography>
            </Box>
          ) : (
            // Mobile version - more compact
            <Box sx={{ 
              display: 'flex', 
              flexDirection: 'column', 
              alignItems: 'flex-end',
              mr: 1
            }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
                Budget: <strong>${userBudget.toFixed(2)}</strong>
              </Typography>
            </Box>
          )}

          {/* Cart Button with Count and Total */}
          <Box>
            {cartItemCount > 0 ? (
              <Button
                variant="contained"
                onClick={() => navigate('/cart')}
                startIcon={
                  <Badge badgeContent={cartItemCount} color="secondary">
                    <ShoppingCartIcon />
                  </Badge>
                }
                sx={{ 
                  bgcolor: 'primary.main',
                  color: 'white',
                  px: 2,
                  py: 0.75,
                  borderRadius: 1,
                  '&:hover': {
                    bgcolor: 'primary.dark'
                  }
                }}
              >
                ${cartTotal.toFixed(2)}
              </Button>
            ) : (
              <IconButton 
                onClick={() => navigate('/cart')}
                sx={{ color: 'text.primary' }}
              >
                <ShoppingCartIcon />
              </IconButton>
            )}
          </Box>

          {!isMobile && (
            <IconButton
              onClick={() => navigate('/profile')}
              sx={{ ml: 1, color: 'text.primary' }}
            >
              <AccountCircleIcon />
            </IconButton>
          )}
        </Toolbar>
      </AppBar>

      {/* Mobile Drawer Menu */}
      <MobileDrawer />

      {/* Main Content */}
      <Box
        component="main"
        sx={{
          flexGrow: 1,
          mt: 8,
          mb: isMobile ? 7 : 0,
          minHeight: 'calc(100vh - 64px)',
        }}
      >
        <Container maxWidth="lg" sx={{ py: isMobile ? 2 : 4 }}>
          <Outlet />
        </Container>
      </Box>

      {/* Footer */}
      <Footer />

      {/* Mobile Bottom Navigation */}
      {isMobile && (
        <Paper 
          sx={{ 
            position: 'fixed', 
            bottom: 0, 
            left: 0, 
            right: 0,
            zIndex: 1200
          }} 
          elevation={3}
        >
          <BottomNavigation
            showLabels
            value={bottomNavValue}
            onChange={(event, newValue) => {
              setBottomNavValue(newValue);
              navigate(menuItems[newValue].path);
            }}
          >
            <BottomNavigationAction label="Home" icon={<HomeIcon />} />
            <BottomNavigationAction 
              label="Cart" 
              icon={
                <Badge badgeContent={cartItemCount} color="secondary">
                  <ShoppingCartIcon />
                </Badge>
              } 
            />
            <BottomNavigationAction label="Profile" icon={<PersonIcon />} />
          </BottomNavigation>
        </Paper>
      )}

      {/* Cart Drawer */}
      <CartDrawer />
    </Box>
  );
};

export default Layout;
