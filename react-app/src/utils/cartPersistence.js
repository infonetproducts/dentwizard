// Cart persistence utility functions
// This ensures cart persists even after PHP session expires

const CART_STORAGE_KEY = 'dentwizard_cart';
const CART_EXPIRY_DAYS = 30; // Cart persists for 30 days

export const cartPersistence = {
  // Save cart to localStorage with expiry
  saveCart: (cartData) => {
    try {
      // Validate cartData before saving
      if (!cartData || typeof cartData !== 'object') {
        console.warn('Invalid cart data provided to saveCart:', cartData);
        return;
      }
      
      const cartToSave = {
        items: cartData.items || [],
        summary: cartData.summary || {},
        savedAt: new Date().toISOString(),
        expiresAt: new Date(Date.now() + (CART_EXPIRY_DAYS * 24 * 60 * 60 * 1000)).toISOString()
      };
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartToSave));
    } catch (error) {
      console.error('Failed to save cart to localStorage:', error);
    }
  },

  // Load cart from localStorage
  loadCart: () => {
    try {
      const savedCart = localStorage.getItem(CART_STORAGE_KEY);
      if (!savedCart) return null;
      
      const cartData = JSON.parse(savedCart);
      
      // Check if cart has expired
      if (cartData.expiresAt && new Date(cartData.expiresAt) < new Date()) {
        localStorage.removeItem(CART_STORAGE_KEY);
        return null;
      }
      
      return {
        items: cartData.items || [],
        summary: cartData.summary || {}
      };
    } catch (error) {
      console.error('Failed to load cart from localStorage:', error);
      return null;
    }
  },

  // Clear cart from localStorage
  clearCart: () => {
    try {
      localStorage.removeItem(CART_STORAGE_KEY);
    } catch (error) {
      console.error('Failed to clear cart from localStorage:', error);
    }
  },

  // Sync local cart with server
  syncCart: async (api, localCart) => {
    try {
      // Send local cart items to server to restore session
      if (localCart && localCart.items && localCart.items.length > 0) {
        const response = await api.post('/cart/sync', {
          items: localCart.items
        });
        return response.data;
      }
    } catch (error) {
      console.error('Failed to sync cart with server:', error);
    }
    return null;
  }
};

export default cartPersistence;