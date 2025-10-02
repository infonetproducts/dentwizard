import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../services/api';

// Async thunks for cart operations
export const fetchCart = createAsyncThunk(
  'cart/fetchCart',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/cart/get.php');
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to fetch cart');
    }
  }
);

export const addToCart = createAsyncThunk(
  'cart/addToCart',
  async ({ productId, quantity, options }, { rejectWithValue }) => {
    try {
      const response = await api.post('/cart/add.php', {
        product_id: productId,
        quantity,
        ...options
      });
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to add to cart');
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

export const removeDiscount = createAsyncThunk(
  'cart/removeDiscount',
  async ({ type }, { rejectWithValue }) => {
    try {
      const response = await api.post('/cart/remove-discount.php', {
        discount_type: type
      });
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to remove discount');
    }
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
    clearCart: (state) => {
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
    },
    updateQuantity: (state, action) => {
      const { itemId, quantity } = action.payload;
      const item = state.items.find(item => item.id === itemId);
      if (item) {
        item.quantity = quantity;
      }
    }
  },
  extraReducers: (builder) => {
    builder
      // Fetch Cart
      .addCase(fetchCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchCart.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload.cart_items || [];
        state.summary = action.payload.cart_summary || state.summary;
        state.budget = action.payload.budget || state.budget;
      })
      .addCase(fetchCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      // Add to Cart
      .addCase(addToCart.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(addToCart.fulfilled, (state) => {
        state.loading = false;
      })
      .addCase(addToCart.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
  }
});

export const { clearCart, updateQuantity } = cartSlice.actions;
export default cartSlice.reducer;
