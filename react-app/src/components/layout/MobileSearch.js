import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  IconButton,
  Dialog,
  DialogContent,
  TextField,
  InputAdornment,
  List,
  ListItem,
  ListItemText,
  Box,
  Typography
} from '@mui/material';
import {
  Search as SearchIcon,
  Close as CloseIcon
} from '@mui/icons-material';
import { useDebounce } from '../../hooks/useDebounce';
import api from '../../services/api';

const MobileSearch = () => {
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  
  const debouncedSearchTerm = useDebounce(searchTerm, 500);

  React.useEffect(() => {
    if (debouncedSearchTerm) {
      searchProducts();
    } else {
      setResults([]);
    }
  }, [debouncedSearchTerm]);

  const searchProducts = async () => {
    setLoading(true);
    try {
      const response = await api.get(`/search/products.php?q=${debouncedSearchTerm}`);
      setResults(response.data.data || []);
    } catch (error) {
      console.error('Search error:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleProductClick = (productId) => {
    setOpen(false);
    setSearchTerm('');
    navigate(`/products/${productId}`);
  };

  return (
    <>
      <IconButton onClick={() => setOpen(true)} sx={{ color: 'text.primary' }}>
        <SearchIcon />
      </IconButton>

      <Dialog
        fullScreen
        open={open}
        onClose={() => setOpen(false)}
      >
        <Box sx={{ p: 2 }}>
          <TextField
            fullWidth
            autoFocus
            placeholder="Search products..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <SearchIcon />
                </InputAdornment>
              ),
              endAdornment: (
                <InputAdornment position="end">
                  <IconButton onClick={() => setOpen(false)}>
                    <CloseIcon />
                  </IconButton>
                </InputAdornment>
              ),
            }}
          />
        </Box>

        <DialogContent>
          {loading && (
            <Typography color="text.secondary" align="center">
              Searching...
            </Typography>
          )}
          
          {!loading && results.length === 0 && searchTerm && (
            <Typography color="text.secondary" align="center">
              No products found
            </Typography>
          )}

          <List>
            {results.map((product) => (
              <ListItem
                key={product.id}
                button
                onClick={() => handleProductClick(product.id)}
              >
                <ListItemText
                  primary={product.name}
                  secondary={`$${product.price}`}
                />
              </ListItem>
            ))}
          </List>
        </DialogContent>
      </Dialog>
    </>
  );
};

export default MobileSearch;
