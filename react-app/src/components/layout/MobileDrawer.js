import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import {
  Box,
  Drawer,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  ListItemButton,
  Divider,
  Avatar,
  IconButton,
  Typography,
  Collapse
} from '@mui/material';
import {
  Close as CloseIcon,
  Home as HomeIcon,
  ShoppingCart as ShoppingCartIcon,
  Person as PersonIcon,
  Logout as LogoutIcon,
  ChevronRight as ChevronRightIcon,
  ExpandMore as ExpandMoreIcon
} from '@mui/icons-material';
import { useMsal } from '@azure/msal-react';
import { closeMobileMenu } from '../../store/slices/uiSlice';
import { fetchCategories } from '../../store/slices/productsSlice';
import dentwizardLogo from '../../images/dentwizard.png';

const MobileDrawer = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useDispatch();
  const { instance } = useMsal();
  
  const { isMobileMenuOpen } = useSelector(state => state.ui);
  const { user } = useSelector(state => state.auth);
  const { categories = [] } = useSelector(state => state.products || {});
  
  const [expandedCategories, setExpandedCategories] = useState({});

  useEffect(() => {
    dispatch(fetchCategories());
  }, [dispatch]);

  const handleLogout = async () => {
    await instance.logoutRedirect();
  };

  const handleNavigate = (path) => {
    navigate(path);
    dispatch(closeMobileMenu());
  };

  const handleCategoryClick = (categoryId, hasSubcategories) => {
    if (hasSubcategories) {
      setExpandedCategories(prev => ({
        ...prev,
        [categoryId]: !prev[categoryId]
      }));
    } else {
      handleNavigate(`/products?category=${categoryId}`);
    }
  };

  // Get parent categories only
  const parentCategories = categories.filter(cat => 
    cat && (cat.parent_id === 0 || cat.parent_id === null)
  );
  
  const getSubcategories = (parentId) => {
    return categories.filter(cat => cat && cat.parent_id === parentId);
  };

  return (
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
      {/* Header with Logo */}
      <Box sx={{ 
        p: 2, 
        display: 'flex', 
        justifyContent: 'space-between', 
        alignItems: 'center',
        borderBottom: 1,
        borderColor: 'divider'
      }}>
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
      
      {/* User Info */}
      {user && (
        <>
          <Box sx={{ p: 2, bgcolor: 'grey.50' }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
              <Avatar sx={{ bgcolor: 'primary.main' }}>
                {user.name?.charAt(0)}
              </Avatar>
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
          <Divider />
        </>
      )}
      
      {/* Main Navigation */}
      <List>
        <ListItem disablePadding>
          <ListItemButton
            onClick={() => handleNavigate('/')}
            selected={location.pathname === '/'}
          >
            <ListItemIcon>
              <HomeIcon color="primary" />
            </ListItemIcon>
            <ListItemText primary="Home" />
          </ListItemButton>
        </ListItem>
      </List>
      
      <Divider />
      
      {/* Categories Section */}
      <Box sx={{ py: 1 }}>
        <Typography 
          variant="overline" 
          sx={{ 
            px: 2, 
            color: 'text.secondary',
            fontWeight: 600,
            fontSize: '0.7rem'
          }}
        >
          Shop by Category
        </Typography>
        
        <List disablePadding>
          {parentCategories.map(category => {
            const subcategories = getSubcategories(category.id);
            const hasSubcategories = subcategories.length > 0;
            const isExpanded = expandedCategories[category.id];
            
            return (
              <Box key={category.id}>
                <ListItem disablePadding>
                  <ListItemButton
                    onClick={() => handleCategoryClick(category.id, hasSubcategories)}
                    sx={{ pl: 2 }}
                  >
                    <ListItemText 
                      primary={category.name}
                      primaryTypographyProps={{
                        fontWeight: hasSubcategories ? 600 : 400,
                        fontSize: '0.95rem'
                      }}
                    />
                    {hasSubcategories && (
                      isExpanded ? <ExpandMoreIcon /> : <ChevronRightIcon />
                    )}
                  </ListItemButton>
                </ListItem>
                
                {/* Subcategories */}
                {hasSubcategories && (
                  <Collapse in={isExpanded} timeout="auto" unmountOnExit>
                    <List component="div" disablePadding>
                      {subcategories.map(subcat => (
                        <ListItem key={subcat.id} disablePadding>
                          <ListItemButton
                            onClick={() => handleNavigate(`/products?category=${subcat.id}`)}
                            sx={{ pl: 5 }}
                          >
                            <ListItemText 
                              primary={subcat.name}
                              primaryTypographyProps={{
                                fontSize: '0.9rem',
                                color: 'text.secondary'
                              }}
                            />
                          </ListItemButton>
                        </ListItem>
                      ))}
                    </List>
                  </Collapse>
                )}
              </Box>
            );
          })}
        </List>
      </Box>
      
      <Divider />
      
      {/* Account Actions */}
      <List>
        <ListItem disablePadding>
          <ListItemButton
            onClick={() => handleNavigate('/profile')}
            selected={location.pathname === '/profile'}
          >
            <ListItemIcon>
              <PersonIcon color="primary" />
            </ListItemIcon>
            <ListItemText primary="Profile" />
          </ListItemButton>
        </ListItem>
        
        <ListItem disablePadding>
          <ListItemButton
            onClick={() => handleNavigate('/cart')}
            selected={location.pathname === '/cart'}
          >
            <ListItemIcon>
              <ShoppingCartIcon color="primary" />
            </ListItemIcon>
            <ListItemText primary="Cart" />
          </ListItemButton>
        </ListItem>
      </List>
      
      <Divider />
      
      {/* Logout */}
      <List>
        <ListItem disablePadding>
          <ListItemButton onClick={handleLogout}>
            <ListItemIcon>
              <LogoutIcon color="error" />
            </ListItemIcon>
            <ListItemText 
              primary="Logout"
              primaryTypographyProps={{
                color: 'error.main'
              }}
            />
          </ListItemButton>
        </ListItem>
      </List>
    </Drawer>
  );
};

export default MobileDrawer;
