import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../../services/api';

// Fetch user profile
export const fetchUserProfile = createAsyncThunk(
  'profile/fetchUserProfile',
  async (userId = null, { rejectWithValue }) => {
    try {
      // Use the actual profile endpoint that respects the logged-in user's session
      const response = await api.get('/user/profile.php');
      return response.data; // Return the full response including profile and budget
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to fetch profile');
    }
  }
);

// Update user profile
export const updateUserProfile = createAsyncThunk(
  'profile/updateUserProfile',
  async (profileData, { rejectWithValue }) => {
    try {
      const response = await api.post('/users/update-profile.php', profileData);
      return response.data.data;
    } catch (error) {
      return rejectWithValue(error.response?.data?.error || 'Failed to update profile');
    }
  }
);

const profileSlice = createSlice({
  name: 'profile',
  initialState: {
    user: null,
    budget: null,
    shippingAddress: null,
    orderHistory: [],
    loading: false,
    error: null
  },
  reducers: {
    clearProfile: (state) => {
      state.user = null;
      state.budget = null;
      state.shippingAddress = null;
      state.orderHistory = [];
      state.error = null;
    }
  },
  extraReducers: (builder) => {
    builder
      // Fetch profile
      .addCase(fetchUserProfile.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchUserProfile.fulfilled, (state, action) => {
        state.loading = false;
        const data = action.payload; // Now contains { success, profile, budget }
        
        // Check if data exists and was successful
        if (data && data.success && data.profile) {
          // Map the actual API response structure
          state.user = {
            id: data.profile.id || 1,
            name: data.profile.name || 'User',
            email: data.profile.email || '',
            phone: data.profile.phone || '',
            department: data.profile.department || '',
            company: data.profile.company || '',
            userType: data.profile.userType || '',
            employeeType: data.profile.employeeType || '',
            clientId: data.profile.clientId || 0
          };
          
          // Map the budget data from the response
          if (data.budget) {
            state.budget = {
              has_budget: data.budget.budget_amount > 0,
              budget_limit: data.budget.budget_amount || 0,
              budget_balance: data.budget.budget_balance || 0,
              budget_used: (data.budget.budget_amount - data.budget.budget_balance) || 0,
              budget_percentage: data.budget.budget_amount > 0 
                ? ((data.budget.budget_amount - data.budget.budget_balance) / data.budget.budget_amount * 100) 
                : 0,
              can_order: true,
              recurring: data.budget.recurring || false,
              renewal_date: data.budget.renewal_date || null
            };
          }
          
          state.shippingAddress = data.profile.shippingAddress || {};
          state.addresses = data.profile.addresses || [];
          state.orderHistory = data.orders || [];
        }
      })
      .addCase(fetchUserProfile.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      // Update profile
      .addCase(updateUserProfile.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(updateUserProfile.fulfilled, (state, action) => {
        state.loading = false;
        state.user = action.payload.user;
        state.shippingAddress = action.payload.shipping_address;
      })
      .addCase(updateUserProfile.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
  }
});

export const { clearProfile } = profileSlice.actions;
export default profileSlice.reducer;