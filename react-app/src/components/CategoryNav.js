import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSelector, useDispatch } from 'react-redux';
import {
  Box,
  Button,
  Container,
  Paper,
  Typography,
  useTheme,
  useMediaQuery
} from '@mui/material';
import {
  ExpandMore as ExpandMoreIcon,
  ExpandLess as ExpandLessIcon
} from '@mui/icons-material';
import { fetchCategories } from '../store/slices/productsSlice';

const CategoryNav = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  
  const { categories = [] } = useSelector(state => state.products || {});
  const [hoveredCategory, setHoveredCategory] = useState(null);
  const [expandedCategory, setExpandedCategory] = useState(null); // For mobile touch
  const [hoverTimeout, setHoverTimeout] = useState(null); // For delayed hide
  
  useEffect(() => {
    dispatch(fetchCategories());
  }, [dispatch]);
  
  // Cleanup timeout on unmount
  useEffect(() => {
    return () => {
      if (hoverTimeout) {
        clearTimeout(hoverTimeout);
      }
    };
  }, [hoverTimeout]);
  
  // Get parent categories only
  const parentCategories = categories.filter(cat => 
    cat && (cat.parent_id === 0 || cat.parent_id === null)
  );
  
  const getSubcategories = (parentId) => {
    return categories.filter(cat => cat && cat.parent_id === parentId);
  };
  
  // Handle mouse enter with immediate show
  const handleMouseEnter = (categoryId) => {
    if (isMobile) return;
    
    // Clear any pending hide timeout
    if (hoverTimeout) {
      clearTimeout(hoverTimeout);
      setHoverTimeout(null);
    }
    
    setHoveredCategory(categoryId);
  };
  
  // Handle mouse leave with delayed hide
  const handleMouseLeave = () => {
    if (isMobile) return;
    
    // Add delay before hiding to allow user to move to dropdown
    const timeout = setTimeout(() => {
      setHoveredCategory(null);
    }, 300); // 300ms delay
    
    setHoverTimeout(timeout);
  };
  
  const handleCategoryClick = (categoryId, event) => {
    const subcategories = getSubcategories(categoryId);
    
    if (isMobile && subcategories.length > 0) {
      // On mobile with subcategories, toggle expansion instead of navigate
      event.preventDefault();
      setExpandedCategory(expandedCategory === categoryId ? null : categoryId);
    } else {
      // Navigate directly if no subcategories or on desktop
      navigate(`/products?category=${categoryId}`);
    }
  };
  
  const handleSubcategoryClick = (categoryId) => {
    navigate(`/products?category=${categoryId}`);
    setExpandedCategory(null); // Close dropdown after selection
    setHoveredCategory(null); // Close dropdown on desktop too
  };
  
  const isDropdownOpen = (categoryId) => {
    if (isMobile) {
      return expandedCategory === categoryId;
    }
    return hoveredCategory === categoryId;
  };  
  return (
    <Box 
      sx={{ 
        backgroundColor: 'background.paper',
        border: '1px solid',
        borderColor: 'divider',
        borderRadius: 2,
        position: isMobile ? 'relative' : 'sticky',
        top: 0,
        zIndex: 1100,
        mx: 2,
        mt: 2
      }}
    >
      <Container>
        <Box sx={{ 
          display: 'flex', 
          gap: isMobile ? 1 : 2, 
          py: 1.5, 
          alignItems: 'center', 
          flexWrap: 'wrap',
          justifyContent: isMobile ? 'space-around' : 'flex-start'
        }}>
          {parentCategories.map(category => {
            const subcategories = getSubcategories(category.id);
            const hasSubcategories = subcategories.length > 0;
            const isOpen = isDropdownOpen(category.id);
            
            return (              <Box
                key={category.id}
                sx={{ position: 'relative' }}
                onMouseEnter={() => handleMouseEnter(category.id)}
                onMouseLeave={handleMouseLeave}
              >
                <Button
                  onClick={(e) => handleCategoryClick(category.id, e)}
                  endIcon={
                    isMobile && hasSubcategories ? (
                      isOpen ? <ExpandLessIcon fontSize="small" /> : <ExpandMoreIcon fontSize="small" />
                    ) : null
                  }
                  sx={{
                    color: 'text.primary',
                    textTransform: 'none',
                    fontSize: isMobile ? '0.75rem' : '0.875rem',
                    px: isMobile ? 1 : 2,
                    minWidth: isMobile ? 'auto' : 'unset',
                    '&:hover': {
                      backgroundColor: 'action.hover'
                    }
                  }}
                >
                  {category.name}
                </Button>                
                {/* Dropdown for subcategories */}
                {hasSubcategories && isOpen && (
                  <Paper
                    elevation={3}
                    onMouseEnter={() => handleMouseEnter(category.id)}
                    onMouseLeave={handleMouseLeave}
                    sx={{
                      position: 'absolute',
                      top: '100%',
                      left: isMobile ? '50%' : 0,
                      transform: isMobile ? 'translateX(-50%)' : 'none',
                      minWidth: isMobile ? 150 : 200,
                      zIndex: 1200,
                      mt: 0, // No gap - flush with button
                      pt: 0.5, // Small internal padding for visual spacing
                      maxHeight: '60vh',
                      overflowY: 'auto',
                      // Ensure dropdown stays within viewport on mobile
                      ...(isMobile && {
                        '@media (max-width: 400px)': {
                          position: 'fixed',
                          left: '10px',
                          right: '10px',
                          width: 'auto',
                          transform: 'none'
                        }
                      })
                    }}
                    onClick={(e) => e.stopPropagation()} // Prevent closing when clicking inside
                  >                    {subcategories.map(subcat => (
                      <Box
                        key={subcat.id}
                        onClick={() => handleSubcategoryClick(subcat.id)}
                        sx={{
                          px: 2,
                          py: isMobile ? 1.5 : 1, // Larger touch targets on mobile
                          cursor: 'pointer',
                          borderBottom: isMobile ? '1px solid' : 'none',
                          borderColor: 'divider',
                          '&:hover': {
                            backgroundColor: 'action.hover'
                          },
                          '&:last-child': {
                            borderBottom: 'none'
                          }
                        }}
                      >
                        <Typography variant={isMobile ? "body1" : "body2"}>
                          {subcat.name}
                        </Typography>
                      </Box>
                    ))}
                  </Paper>
                )}
              </Box>
            );
          })}
        </Box>
      </Container>
    </Box>
  );
};

export default CategoryNav;