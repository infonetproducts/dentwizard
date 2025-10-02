import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSelector, useDispatch } from 'react-redux';
import {
  Box,
  Button,
  Container,
  Paper,
  Typography
} from '@mui/material';
import { fetchCategories } from '../store/slices/productsSlice';

const CategoryNav = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { categories = [] } = useSelector(state => state.products || {});
  const [hoveredCategory, setHoveredCategory] = useState(null);
  
  useEffect(() => {
    dispatch(fetchCategories());
  }, [dispatch]);
  
  // Get parent categories only
  const parentCategories = categories.filter(cat => 
    cat && (cat.parent_id === 0 || cat.parent_id === null)
  );
  
  const getSubcategories = (parentId) => {
    return categories.filter(cat => cat && cat.parent_id === parentId);
  };
  
  const handleCategoryClick = (categoryId) => {
    navigate(`/products?category=${categoryId}`);
  };
  
  return (
    <Box 
      sx={{ 
        backgroundColor: 'background.paper',
        border: '1px solid',
        borderColor: 'divider',
        borderRadius: 2,
        position: 'sticky',
        top: 0,
        zIndex: 1100,
        mx: 2,
        mt: 2
      }}
    >
      <Container>
        <Box sx={{ display: 'flex', gap: 2, py: 1.5, alignItems: 'center', flexWrap: 'wrap' }}>
          {parentCategories.map(category => {
            const subcategories = getSubcategories(category.id);
            
            return (
              <Box
                key={category.id}
                sx={{ position: 'relative' }}
                onMouseEnter={() => setHoveredCategory(category.id)}
                onMouseLeave={() => setHoveredCategory(null)}
              >
                <Button
                  onClick={() => handleCategoryClick(category.id)}
                  sx={{
                    color: 'text.primary',
                    textTransform: 'none',
                    fontSize: '0.875rem',
                    px: 2,
                    '&:hover': {
                      backgroundColor: 'action.hover'
                    }
                  }}
                >
                  {category.name}
                </Button>
                
                {/* Simple dropdown for subcategories */}
                {subcategories.length > 0 && hoveredCategory === category.id && (
                  <Paper
                    elevation={3}
                    sx={{
                      position: 'absolute',
                      top: '100%',
                      left: 0,
                      minWidth: 200,
                      zIndex: 1200,
                      mt: 0.5
                    }}
                  >
                    {subcategories.map(subcat => (
                      <Box
                        key={subcat.id}
                        onClick={() => handleCategoryClick(subcat.id)}
                        sx={{
                          px: 2,
                          py: 1,
                          cursor: 'pointer',
                          '&:hover': {
                            backgroundColor: 'action.hover'
                          }
                        }}
                      >
                        <Typography variant="body2">
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