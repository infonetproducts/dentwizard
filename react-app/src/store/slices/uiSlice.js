import { createSlice } from '@reduxjs/toolkit';

const uiSlice = createSlice({
  name: 'ui',
  initialState: {
    isMobileMenuOpen: false,
    isCartDrawerOpen: false,
    isSearchOpen: false,
    selectedCategory: null,
    viewMode: 'grid', // grid or list
    theme: 'light',
    notifications: [],
    loading: {
      global: false,
      products: false,
      cart: false,
      checkout: false
    }
  },
  reducers: {
    toggleMobileMenu: (state) => {
      state.isMobileMenuOpen = !state.isMobileMenuOpen;
    },
    closeMobileMenu: (state) => {
      state.isMobileMenuOpen = false;
    },
    toggleCartDrawer: (state) => {
      state.isCartDrawerOpen = !state.isCartDrawerOpen;
    },
    closeCartDrawer: (state) => {
      state.isCartDrawerOpen = false;
    },
    toggleSearch: (state) => {
      state.isSearchOpen = !state.isSearchOpen;
    },
    setViewMode: (state, action) => {
      state.viewMode = action.payload;
    },
    setSelectedCategory: (state, action) => {
      state.selectedCategory = action.payload;
    },
    setLoading: (state, action) => {
      const { type, value } = action.payload;
      state.loading[type] = value;
    },
    addNotification: (state, action) => {
      state.notifications.push({
        id: Date.now(),
        ...action.payload
      });
    },
    removeNotification: (state, action) => {
      state.notifications = state.notifications.filter(
        notif => notif.id !== action.payload
      );
    }
  }
});

export const {
  toggleMobileMenu,
  closeMobileMenu,
  toggleCartDrawer,
  closeCartDrawer,
  toggleSearch,
  setViewMode,
  setSelectedCategory,
  setLoading,
  addNotification,
  removeNotification
} = uiSlice.actions;

export default uiSlice.reducer;
