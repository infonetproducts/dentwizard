// Address Service - Add this to src/services/addressService.js
import api from './api';

const addressService = {
  // Get all saved addresses for the user
  getSavedAddresses: async () => {
    try {
      const response = await api.get('/user/addresses.php');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching addresses:', error);
      return [];
    }
  },

  // Save a new address
  saveAddress: async (addressData) => {
    try {
      const response = await api.post('/user/addresses.php', addressData);
      return response.data;
    } catch (error) {
      console.error('Error saving address:', error);
      throw error;
    }
  },

  // Update an existing address
  updateAddress: async (id, addressData) => {
    try {
      const response = await api.put(`/user/addresses/${id}`, addressData);
      return response.data;
    } catch (error) {
      console.error('Error updating address:', error);
      throw error;
    }
  },

  // Delete an address
  deleteAddress: async (id) => {
    try {
      const response = await api.delete(`/user/addresses/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error deleting address:', error);
      throw error;
    }
  },

  // Set as default address
  setDefaultAddress: async (id) => {
    try {
      const response = await api.post(`/user/addresses/${id}/default`);
      return response.data;
    } catch (error) {
      console.error('Error setting default address:', error);
      throw error;
    }
  }
};

export default addressService;