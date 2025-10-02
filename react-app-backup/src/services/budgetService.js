import api from './api';
import { store } from '../store/store';

class BudgetService {
  /**
   * Get user's budget balance from Redux store
   * @returns {Promise<Object>} Budget information
   */
  async getUserBudget() {
    try {
      // Get budget from Redux store instead of making an API call
      const state = store.getState();
      const profileBudget = state.profile.budget;
      
      if (profileBudget) {
        return {
          hasBudget: true,
          balance: profileBudget.budget_balance || 0,
          available: profileBudget.budget_balance || 0,
          allocated: profileBudget.budget_amount || 0,
          used: (profileBudget.budget_amount || 0) - (profileBudget.budget_balance || 0),
          currency: 'USD'
        };
      }
      
      // If no budget in store, try fetching profile
      const response = await api.get('/user/profile.php');
      if (response.data && response.data.data && response.data.data.budget) {
        const budget = response.data.data.budget;
        return {
          hasBudget: true,
          balance: budget.budget_balance || 0,
          available: budget.budget_balance || 0,
          allocated: budget.budget_amount || 0,
          used: (budget.budget_amount || 0) - (budget.budget_balance || 0),
          currency: 'USD'
        };
      }
      
      // No budget found
      return {
        hasBudget: false,
        balance: 0,
        available: 0,
        allocated: 0,
        used: 0,
        currency: 'USD'
      };
    } catch (error) {
      console.error('Error fetching budget:', error);
      return {
        hasBudget: false,
        balance: 0,
        available: 0,
        allocated: 0,
        used: 0,
        currency: 'USD'
      };
    }
  }

  /**
   * Check if user has sufficient budget for an order
   * @param {Number} orderTotal - Total amount of the order
   * @returns {Promise<Object>} Budget check result
   */  async checkBudgetForOrder(orderTotal) {
    try {
      const budget = await this.getUserBudget();
      
      if (!budget.hasBudget) {
        return {
          canProceed: true,
          hasBudget: false,
          message: 'No budget restrictions'
        };
      }

      const hasEnoughBudget = budget.balance >= orderTotal;
      
      return {
        canProceed: hasEnoughBudget,
        hasBudget: true,
        balance: budget.balance,
        orderTotal: orderTotal,
        shortage: hasEnoughBudget ? 0 : (orderTotal - budget.balance),
        message: hasEnoughBudget 
          ? `Budget available: $${budget.balance.toFixed(2)}` 
          : `Insufficient budget. You need $${(orderTotal - budget.balance).toFixed(2)} more.`
      };
    } catch (error) {
      console.error('Budget check error:', error);
      // In case of error, allow the order to proceed but log the issue
      return {
        canProceed: true,
        hasBudget: false,
        error: true,
        message: 'Could not verify budget'
      };
    }
  }
  /**
   * Update user's budget after order placement
   * @param {Number} amount - Amount to deduct from budget
   * @param {String} orderId - Order ID for reference
   * @returns {Promise<Object>} Update result
   */
  async deductFromBudget(amount, orderId) {
    try {
      const response = await api.post('/user/budget/deduct.php', {
        amount: amount,
        order_id: orderId,
        description: `Order #${orderId}`
      });

      return {
        success: response.data.success || false,
        newBalance: response.data.new_balance || 0,
        message: response.data.message || 'Budget updated'
      };
    } catch (error) {
      console.error('Budget deduction error:', error);
      return {
        success: false,
        error: error.message,
        message: 'Failed to update budget'
      };
    }
  }

  /**
   * Get budget transaction history
   * @param {Number} limit - Number of transactions to fetch
   * @returns {Promise<Array>} Transaction history
   */
  async getBudgetHistory(limit = 10) {
    try {
      const response = await api.get('/user/budget/history.php', {
        params: { limit }
      });

      return response.data.transactions || [];
    } catch (error) {
      console.error('Error fetching budget history:', error);
      return [];
    }
  }
}

export default new BudgetService();
