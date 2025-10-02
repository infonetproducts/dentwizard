import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../services/api';

export const fetchProducts = createAsyncThunk(
  'products/fetchProducts',
  async ({ category, search, limit = 50, page = 1 }, { rejectWithValue }) => {
    try {
      const params = new URLSearchParams();
      // API expects category_id, not category
      if (category) params.append('category_id', category);
      if (search) params.append('search', search);
      params.append('limit', limit);
      params.append('page', page);
      
      const response = await api.get(`/products/list.php?${params}`);
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to fetch products');
    }
  }
);

export const fetchCategories = createAsyncThunk(
  'products/fetchCategories',
  async (_, { rejectWithValue }) => {
    try {
      const response = await api.get('/categories/list.php');
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to fetch categories');
    }
  }
);

export const fetchProductDetail = createAsyncThunk(
  'products/fetchProductDetail',
  async (productId, { rejectWithValue }) => {
    try {
      const response = await api.get(`/products/detail.php?id=${productId}`);
      return response.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to fetch product details');
    }
  }
);

const productsSlice = createSlice({
  name: 'products',
  initialState: {
    items: [],
    currentProduct: null,
    categories: [],
    categoriesHierarchy: [],
    categoriesLoading: false,
    loading: false,
    error: null,
    filters: {
      category: null,
      search: '',
      sortBy: 'name'
    },
    pagination: {
      page: 1,
      limit: 50,
      total: 0
    }
  },
  reducers: {
    setFilters: (state, action) => {
      state.filters = { ...state.filters, ...action.payload };
    },
    clearFilters: (state) => {
      state.filters = {
        category: null,
        search: '',
        sortBy: 'name'
      };
    },
    setCurrentProduct: (state, action) => {
      state.currentProduct = action.payload;
    }
  },
  extraReducers: (builder) => {
    builder
      // Fetch Products
      .addCase(fetchProducts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchProducts.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload.data || [];
        state.pagination.total = action.payload.total || 0;
      })
      .addCase(fetchProducts.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      // Fetch Categories
      .addCase(fetchCategories.pending, (state) => {
        state.categoriesLoading = true;
      })
      .addCase(fetchCategories.fulfilled, (state, action) => {
        state.categoriesLoading = false;
        state.categories = action.payload.data || [];
        state.categoriesHierarchy = action.payload.hierarchy || [];
      })
      .addCase(fetchCategories.rejected, (state, action) => {
        state.categoriesLoading = false;
        state.error = action.payload;
      })
      // Fetch Product Detail
      .addCase(fetchProductDetail.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchProductDetail.fulfilled, (state, action) => {
        state.loading = false;
        state.currentProduct = action.payload.data;
      })
      .addCase(fetchProductDetail.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
  }
});

export const { setFilters, clearFilters, setCurrentProduct } = productsSlice.actions;
export default productsSlice.reducer;