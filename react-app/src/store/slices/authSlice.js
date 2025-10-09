import { createSlice } from '@reduxjs/toolkit';

const authSlice = createSlice({
  name: 'auth',
  initialState: {
    user: null,
    isAuthenticated: false,
    loading: false,
    error: null,
    budget: {
      allocated: 0,
      used: 0,
      remaining: 0
    }
  },
  reducers: {
    setUser: (state, action) => {
      // Handle both logout (null) and login cases
      if (action.payload === null || action.payload === undefined) {
        // Logout case - clear user data
        state.user = null;
        state.isAuthenticated = false;
        state.budget = {
          allocated: 0,
          used: 0,
          remaining: 0
        };
      } else {
        // Login case - set user data
        state.user = action.payload.user || action.payload;
        state.isAuthenticated = true;
        state.budget = action.payload.budget || state.budget;
      }
    },
    clearUser: (state) => {
      state.user = null;
      state.isAuthenticated = false;
      state.budget = {
        allocated: 0,
        used: 0,
        remaining: 0
      };
    },
    updateBudget: (state, action) => {
      state.budget = action.payload;
    },
    setLoading: (state, action) => {
      state.loading = action.payload;
    },
    setError: (state, action) => {
      state.error = action.payload;
    }
  }
});

export const { setUser, clearUser, updateBudget, setLoading, setError } = authSlice.actions;
export default authSlice.reducer;
