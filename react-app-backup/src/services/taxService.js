import api from './api';

class TaxService {
  /**
   * Calculate tax using TaxJar API
   * @param {Object} address - Shipping address with state and zip
   * @param {Array} items - Cart items
   * @param {Number} subtotal - Cart subtotal
   * @returns {Promise<Object>} Tax calculation result
   */
  async calculateTax(address, items, subtotal) {
    try {
      // Don't calculate tax if no address
      if (!address.state || !address.zipCode) {
        return {
          tax: 0,
          taxRate: 0,
          taxableAmount: 0
        };
      }

      const response = await api.post('/tax/calculate.php', {
        to_state: address.state,
        to_zip: address.zipCode,
        to_city: address.city,
        amount: subtotal,
        shipping: 0, // Will be calculated separately
        line_items: items.map((item, index) => ({
          id: String(index + 1),
          quantity: item.quantity,
          product_identifier: item.id,
          description: item.name,
          unit_price: item.price,
          discount: 0,
          product_tax_code: item.taxCode || '20010' // Default to clothing code
        }))
      });

      return {
        tax: response.data.tax || 0,
        taxRate: response.data.rate || 0,
        taxableAmount: response.data.taxable_amount || 0,
        breakdown: response.data.breakdown || null
      };
    } catch (error) {
      console.error('Tax calculation error:', error);
      // Fallback to no tax on error
      return {
        tax: 0,
        taxRate: 0,
        taxableAmount: 0,
        error: error.message
      };
    }
  }

  /**
   * Check if an address is in a taxable state
   * @param {String} state - State code
   * @returns {Boolean} Whether the state requires tax collection
   */
  isTaxableState(state) {
    // Based on your PHP code, seems like PA has special tax handling
    const taxableStates = ['PA', 'OH', 'CA', 'NY']; // Add states where you collect tax
    return taxableStates.includes(state);
  }
}

export default new TaxService();
