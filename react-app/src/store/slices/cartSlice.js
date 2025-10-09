import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../services/api';
import cartPersistence from '../../utils/cartPersistence';

// Async thunks for cart operations

// Initialize cart - check localStorage first, then sync with server
export const initializeCart = createAsyncThunk(
  'cart/initialize',
  async (_, { rejectWithValue }) => {
    try {
      // First, try to load from localStorage
      const localCart = cartPersistence.loadCart();
      
      // Try to fetch from server
      try {
        const response = await api.get('/cart/cart.php?action=get');
        const serverCart = response.data.data;
        
        // If server has cart, use it and save to localStorage
        if (serverCart && serverCart.items && serverCart.items.length > 0) {
          cartPersistence.saveCart(serverCart);
          return serverCart;
        }
      } catch (error) {
        console.log('Server cart fetch failed, using local cart');
      }
      
      // If we have a local cart but no server cart, sync it
      if (localCart && localCart.items && localCart.items.length > 0) {
        const syncResult = await cartPersistence.syncCart(api, localCart);
        if (syncResult) {
          return syncResult;
        }
        // If sync fails, still use local cart
        return localCart;
      }
      
      // No cart found anywhere
      return { items: [], summary: {} };
    } catch (error) {
      return rejectWithValue(error.message || 'Failed to initialize cart');
    }
  }
);

export const fetchCart = createAsyncThunk(
  'cart/fetchCart',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/cart/cart.php?action=get');
      // Try different response structures to be more robust
      const cartData = response.data.data || response.data || {};
      
      console.log('fetchCart response:', { 
        fullResponse: response.data,
        extractedCartData: cartData 
      });
      
      // Save to localStorage whenever we fetch (only if cartData is valid)
      if (cartData && typeof cartData === 'object') {
        cartPersistence.saveCart(cartData);
      }
      
      return cartData;
    } catch (error) {
      console.error('fetchCart error:', error);
      // If fetch fails, try loading from localStorage
      const localCart = cartPersistence.loadCart();
      if (localCart) {
        return localCart;
      }
      return rejectWithValue(error.response?.data?.error || 'Failed to fetch cart');
    }
  }
);

export const addToCart = createAsyncThunk(
  'cart/addToCart',
  async ({ productId, quantity, options }, { rejectWithValue }) => {
    try {
      const response = await api.post('/cart/cart.php?action=add', {
        product_id: productId,
        quantity,
        ...options
      });
      console.log('addToCart response:', response.data);
      return response.data;
    } catch (error) {
      console.error('addToCart error:', error);
      return rejectWithValue(error.response?.data?.error || 'Failed to add to cart');
    }
  }
);
export const updateQuantity = createAsyncThunk(
  'cart/updateQuantity',
  async ({ itemId, quantity }, { rejectWithValue }) => {
    try {
      const response = await api.post('/cart/cart.php?action=update', {
        item_id: itemId,
        quantity
      });
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to update quantity');
    }
  }
);

export const removeFromCart = createAsyncThunk(
  'cart/removeFromCart',
  async ({ itemId }, { rejectWithValue }) => {
    try {
      const response = await api.post('/cart/cart.php?action=update', {
        item_id: itemId,
        quantity: 0
      });
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to remove item');
    }
  }
);

export const applyDiscount = createAsyncThunk(
  'cart/applyDiscount',
  async ({ type, code }, { rejectWithValue }) => {
    try {
      const response = await api.post('/cart/apply-discount.php', {
        discount_type: type,
        code: code
      });
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to apply discount');
    }
  }
);

export const clearCart = createAsyncThunk(
  'cart/clear',
  async (_, { dispatch }) => {
    try {
      // Clear server-side cart first
      await api.post('/cart/clear.php');
    } catch (error) {
      console.log('Error clearing server cart:', error);
    }
    // Clear local storage
    cartPersistence.clearCart();
    // Return empty cart
    return { items: [], summary: {} };
  }
);

const cartSlice = createSlice({
  name: 'cart',
  initialState: {
    items: [],
    summary: {
      total_items: 0,
      unique_items: 0,
      subtotal: 0,
      tax: 0,
      shipping: 0,
      total: 0
    },
    budget: {
      has_budget: false,
      remaining: 0
    },
    discounts: {
      gift_card: null,
      promo_code: null
    },
    loading: false,
    error: null
  },
  reducers: {
    clearCartState: (state) => {
      state.items = [];
      state.summary = {
        total_items: 0,
        unique_items: 0,
        subtotal: 0,
        tax: 0,
        shipping: 0,
        total: 0
      };
      state.discounts = {
        gift_card: null,
        promo_code: null
      };
    }
  },
  extraReducers: (builder) => {
    builder
      // Initialize Cart
      .addCase(initializeCart.fulfilled, (state, action) => {
        state.loading = false;
        if (action.payload) {
          state.items = action.payload.items || [];
          state.summary = action.payload.summary || state.summary;
          state.budget = action.payload.budget || state.budget;
          state.discounts = action.payload.discounts || state.discounts;
        }
      })
      // Fetch Cart
      .addCase(fetchCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchCart.fulfilled, (state, action) => {
        state.loading = false;
        console.log('fetchCart.fulfilled - action.payload:', action.payload);
        
        if (action.payload) {
          state.items = action.payload.items || [];
          state.summary = action.payload.summary || state.summary;
          state.budget = action.payload.budget || state.budget;
          state.discounts = action.payload.discounts || state.discounts;
          
          console.log('Cart state updated:', {
            itemCount: state.items.length,
            items: state.items,
            summary: state.summary
          });
          
          // Save to localStorage
          cartPersistence.saveCart({
            items: state.items,
            summary: state.summary
          });
        } else {
          console.warn('fetchCart.fulfilled received null/undefined payload');
        }
      })
      .addCase(fetchCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      // Clear Cart
      .addCase(clearCart.fulfilled, (state) => {
        state.items = [];
        state.summary = {
          total_items: 0,
          unique_items: 0,
          subtotal: 0,
          tax: 0,
          shipping: 0,
          total: 0
        };
        state.discounts = {
          gift_card: null,
          promo_code: null
        };
        state.loading = false;
      })
      // Add to Cart
      .addCase(addToCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(addToCart.fulfilled, (state, action) => {
        state.loading = false;
        // Update cart state with returned data
        // action.payload = response.data, so we need action.payload.data for the cart data
        if (action.payload && action.payload.data) {
          state.items = action.payload.data.items || [];
          if (action.payload.data.summary) {
            state.summary = action.payload.data.summary;
          }
        }
      })
      .addCase(addToCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      // Update Quantity
      .addCase(updateQuantity.fulfilled, (state, action) => {
        if (action.payload) {
          state.items = action.payload.items || [];
          state.summary = action.payload.summary || state.summary;
        }
      })
      // Remove from Cart
      .addCase(removeFromCart.fulfilled, (state, action) => {
        if (action.payload) {
          state.items = action.payload.items || [];
          state.summary = action.payload.summary || state.summary;
        }
      });
  }
});

export const { clearCartState } = cartSlice.actions;
// clearCart thunk is already exported above where it's defined
export default cartSlice.reducer;
