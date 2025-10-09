import api from './api';

class ShippingService {
  /**
   * Get available shipping methods for the current client/user
   * @param {Number} clientId - Client ID (CID)
   * @param {Number} subtotal - Cart subtotal
   * @returns {Promise<Array>} Available shipping methods
   */
  async getShippingMethods(clientId, subtotal) {
    try {
      const response = await api.get('/shipping/methods.php', {
        params: { 
          client_id: clientId,
          subtotal: subtotal 
        }
      });
      
      return response.data.methods || this.getDefaultMethods(clientId);
    } catch (error) {
      console.error('Error fetching shipping methods:', error);
      return this.getDefaultMethods(clientId);
    }
  }

  /**
   * Get default shipping methods based on client ID
   * Based on PHP logic from checkout-delivery.php
   */
  getDefaultMethods(clientId) {
    const methods = [];
    
    // Special client configurations from PHP
    const freeShippingClients = [56, 59, 62, 63, 72, 78, 89, 244]; // Added Dent Wizard (244)
    const pickupOnlyClients = [61, 62, 63];
    
    if (pickupOnlyClients.includes(parseInt(clientId))) {
      // Pickup options
      if (parseInt(clientId) === 61) {
        methods.push({
          id: 'pickup_leader',
          name: 'FREE Pickup',
          description: 'Leader Graphics 1107 Hess Ave, Erie, PA 16503',
          cost: 0,
          delivery_days: '1-2 days'
        });
      } else if (parseInt(clientId) === 62) {
        methods.push({
          id: 'hospital_delivery',
          name: 'Delivered to Titusville Area Hospital',
          description: '10-14 Business Days',
          cost: 0,
          delivery_days: '10-14 days'
        });
      } else if (parseInt(clientId) === 56 || parseInt(clientId) === 63) {
        methods.push({
          id: 'pickup_school',
          name: 'Pickup at School',
          description: 'Available for pickup',
          cost: 0,
          delivery_days: '3-5 days'
        });
      }
    }
    
    // Standard shipping option
    if (freeShippingClients.includes(parseInt(clientId))) {
      methods.push({
        id: 'standard_free',
        name: 'FREE Standard Shipping',
        description: '7-10 business days',
        cost: 0,
        delivery_days: '7-10 days'
      });
    } else {
      methods.push({
        id: 'standard',
        name: 'Standard Shipping',
        description: '7-10 business days',
        cost: 10,
        delivery_days: '7-10 days'
      });
    }
    
    // Add pickup at Leader Graphics on second Friday
    methods.push({
      id: 'pickup_friday',
      name: 'Pickup at Leader Graphics',
      description: 'On second Friday from current date',
      cost: 0,
      delivery_days: '1-2 days'
    });
    
    return methods;
  }

  /**
   * Calculate shipping cost based on method and client
   * @param {String} methodId - Shipping method ID
   * @param {Number} clientId - Client ID
   * @param {Number} subtotal - Cart subtotal
   * @returns {Number} Shipping cost
   */
  calculateShippingCost(methodId, clientId, subtotal) {
    const freeShippingClients = [56, 59, 62, 63, 72, 78, 89, 244]; // Added Dent Wizard (244)
    
    // Free shipping for certain clients
    if (freeShippingClients.includes(clientId)) {
      return 0;
    }
    
    // Method-based costs
    switch(methodId) {
      case 'pickup_leader':
      case 'pickup_school':
      case 'pickup_friday':
      case 'hospital_delivery':
      case 'standard_free':
        return 0;
      case 'standard':
        return 10;
      default:
        return 0;
    }
  }
}

export default new ShippingService();
