/**
 * SSO Authentication Service
 * 
 * Handles SAML 2.0 authentication flow with Azure AD.
 * Works alongside existing standard authentication.
 */

import api from './api';
import { samlConfig, isSSODomain } from '../config/samlConfig';

/**
 * Authentication Service
 * Supports both Standard (email/password) and SSO (SAML) authentication
 */
class SSOAuthService {
  /**
   * Detect authentication type based on email domain
   * @param {string} email - User's email address
   * @returns {Promise<Object>} Auth type and user status
   */
  async detectAuthType(email) {
    try {
      // Check if email domain requires SSO
      const requiresSSO = isSSODomain(email);
      
      // Check with backend if user exists
      const response = await api.post('/auth/check-user.php', { email });
      
      return {
        authType: requiresSSO ? 'sso' : 'standard',
        userExists: response.data.exists,
        requiresSSO,
        message: response.data.message
      };
    } catch (error) {
      console.error('Error detecting auth type:', error);
      
      // Default to standard auth if detection fails
      return {
        authType: 'standard',
        userExists: false,
        requiresSSO: false,
        message: 'Unable to verify user status'
      };
    }
  }

  /**
   * Initiate SAML SSO login
   * Redirects to Azure AD login page
   */
  initiateSSO() {
    const apiBaseUrl = process.env.REACT_APP_API_URL || 'https://dentwizard.lgstore.com/lg/API/v1';
    
    // Store the intended destination after login
    const returnUrl = sessionStorage.getItem('sso_return_url') || '/';
    
    // Redirect to backend SAML login endpoint
    // The PHP backend will generate the SAML request and redirect to Azure AD
    window.location.href = `${apiBaseUrl}/auth/saml/login.php?returnUrl=${encodeURIComponent(returnUrl)}`;
  }

  /**
   * Handle SAML callback after Azure AD authentication
   * @param {string} token - JWT token from backend
   * @param {Object} userData - User data from SAML assertion
   * @returns {Promise<Object>} Processed user data
   */
  async handleSAMLCallback(token, userData) {
    try {
      // Store authentication data
      localStorage.setItem('authToken', token);
      localStorage.setItem('authMethod', 'sso');
      localStorage.setItem('user', JSON.stringify(userData));
      localStorage.setItem('userId', userData.id);
      localStorage.setItem('userName', userData.name);
      localStorage.setItem('userEmail', userData.email);
      
      // Clear SSO return URL from session
      const returnUrl = sessionStorage.getItem('sso_return_url') || '/';
      sessionStorage.removeItem('sso_return_url');
      
      return {
        success: true,
        user: userData,
        token,
        returnUrl,
        authMethod: 'sso'
      };
    } catch (error) {
      console.error('Error handling SAML callback:', error);
      throw new Error('Failed to process SSO login');
    }
  }

  /**
   * Initiate SSO logout
   * Logs out from both the app and Azure AD
   */
  logout() {
    const authMethod = localStorage.getItem('authMethod');
    
    // Clear local storage
    localStorage.removeItem('authToken');
    localStorage.removeItem('authMethod');
    localStorage.removeItem('user');
    localStorage.removeItem('userId');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('session_id');
    
    // If SSO user, also logout from Azure AD
    if (authMethod === 'sso') {
      const apiBaseUrl = process.env.REACT_APP_API_URL || 'https://dentwizard.lgstore.com/lg/API/v1';
      window.location.href = `${apiBaseUrl}/auth/saml/logout.php`;
    } else {
      // Standard logout - just redirect to login
      window.location.href = '/login';
    }
  }

  /**
   * Check if user is authenticated
   * @returns {boolean} True if authenticated
   */
  isAuthenticated() {
    const token = localStorage.getItem('authToken');
    const user = localStorage.getItem('user');
    return !!(token && user);
  }

  /**
   * Get current user data
   * @returns {Object|null} User data or null if not authenticated
   */
  getCurrentUser() {
    try {
      const userStr = localStorage.getItem('user');
      return userStr ? JSON.parse(userStr) : null;
    } catch (error) {
      console.error('Error parsing user data:', error);
      return null;
    }
  }

  /**
   * Get authentication method
   * @returns {string} 'standard', 'sso', or 'dev'
   */
  getAuthMethod() {
    return localStorage.getItem('authMethod') || 'standard';
  }

  /**
   * Set return URL for SSO redirect
   * @param {string} url - URL to return to after SSO login
   */
  setReturnUrl(url) {
    sessionStorage.setItem('sso_return_url', url);
  }
}

// Export singleton instance
const ssoAuthService = new SSOAuthService();
export default ssoAuthService;
