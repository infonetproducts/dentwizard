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
  Drawer,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  ListItemButton,
  Divider,
  Avatar,
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
  Category as CategoryIcon,
  LocalMall as LocalMallIcon,
  Close as CloseIcon,
  Logout as LogoutIcon,
  AccountCircle as AccountCircleIcon
} from '@mui/icons-material';
import { useMsal } from '@azure/msal-react';
import { toggleMobileMenu, closeMobileMenu } from '../../store/slices/uiSlice';
import CartDrawer from './CartDrawer';
import MobileSearch from './MobileSearch';
const Layout = () => {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useDispatch();
  const { instance } = useMsal();
  
  const { isMobileMenuOpen } = useSelector(state => state.ui);
  const { summary } = useSelector(state => state.cart);
  const { user } = useSelector(state => state.auth);
  const cartItemCount = summary?.total_items || 0;

  const [bottomNavValue, setBottomNavValue] = React.useState(0);

  React.useEffect(() => {
    // Update bottom navigation based on current route
    const path = location.pathname;
    if (path === '/') setBottomNavValue(0);
    else if (path === '/cart') setBottomNavValue(1);
    else if (path === '/profile') setBottomNavValue(2);
  }, [location]);

  const handleLogout = async () => {
    await instance.logoutRedirect();
  };

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
            <Box sx={{ display: 'flex', gap: 3, mx: 4 }}>
              <Button onClick={() => navigate('/')} color="inherit">
                Home
              </Button>
            </Box>
          )}

          {isMobile && <MobileSearch />}

          <IconButton 
            onClick={() => navigate('/cart')}
            sx={{ color: 'text.primary' }}
          >
            <Badge badgeContent={cartItemCount} color="secondary">
              <ShoppingCartIcon />
            </Badge>
          </IconButton>

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
      <Drawer
        anchor="left"
        open={isMobileMenuOpen}
        onClose={() => dispatch(closeMobileMenu())}
        sx={{
          '& .MuiDrawer-paper': { 
            width: 280,
            boxSizing: 'border-box'
          }
        }}
      >
        <Box sx={{ p: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <img 
            src={dentwizardLogo} 
            alt="DentWizard" 
            style={{ 
              height: '40px',
              width: 'auto'
            }} 
          />
          <IconButton onClick={() => dispatch(closeMobileMenu())}>
            <CloseIcon />
          </IconButton>
        </Box>
        
        <Divider />
        
        {user && (
          <Box sx={{ p: 2, bgcolor: 'grey.50' }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
              <Avatar>{user.name?.charAt(0)}</Avatar>
              <Box>
                <Typography variant="body2" fontWeight="bold">
                  {user.name}
                </Typography>
                <Typography variant="caption" color="text.secondary">
                  {user.email}
                </Typography>
              </Box>
            </Box>
          </Box>
        )}
        
        <Divider />
        
        <List>
          {menuItems.map((item) => (
            <ListItem key={item.text} disablePadding>
              <ListItemButton
                onClick={() => {
                  navigate(item.path);
                  dispatch(closeMobileMenu());
                }}
                selected={location.pathname === item.path}
              >
                <ListItemIcon sx={{ color: 'primary.main' }}>
                  {item.icon}
                </ListItemIcon>
                <ListItemText primary={item.text} />
              </ListItemButton>
            </ListItem>
          ))}
        </List>
        
        <Divider />
        
        <List>
          <ListItem disablePadding>
            <ListItemButton onClick={handleLogout}>
              <ListItemIcon sx={{ color: 'error.main' }}>
                <LogoutIcon />
              </ListItemIcon>
              <ListItemText primary="Logout" />
            </ListItemButton>
          </ListItem>
        </List>
      </Drawer>

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
