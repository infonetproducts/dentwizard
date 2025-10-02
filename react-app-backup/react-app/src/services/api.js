import axios from 'axios';
import { msalInstance } from '../authConfig';
import toast from 'react-hot-toast';

// Create axios instance
const api = axios.create({
  // In development, proxy handles this. In production, use env variable
  baseURL: process.env.NODE_ENV === 'production' 
    ? process.env.REACT_APP_API_URL 
    : '/lg/API/v1',  // This will be proxied to https://dentwizard.lgstore.com/lg/API/v1
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true,  // CRITICAL: Send cookies with requests for PHP sessions
});

// Request interceptor to add auth token
api.interceptors.request.use(
  async (config) => {
    try {
      // Add token from localStorage if it exists
      const authToken = localStorage.getItem('authToken');
      if (authToken) {
        config.headers['X-Auth-Token'] = authToken;
      }
      
      // Also add user ID for additional verification
      const userId = localStorage.getItem('userId');
      if (userId) {
        config.headers['X-User-Id'] = userId;
      }
      
      // TEMPORARILY DISABLED - API returning 401 with auth header
      /*
      // Check if we're using mock authentication
      if (process.env.REACT_APP_USE_MOCK_AUTH === 'true') {
        // Add a mock token for development
        config.headers.Authorization = 'Bearer mock-token-development';
      } else {
        // Get Azure AD token if user is authenticated
        const accounts = msalInstance.getAllAccounts();
        if (accounts.length > 0) {
          const tokenResponse = await msalInstance.acquireTokenSilent({
            scopes: ['User.Read'],
            account: accounts[0],
          });
          config.headers.Authorization = `Bearer ${tokenResponse.accessToken}`;
        }
      }
      */
      
      // Add session ID if exists
      const sessionId = localStorage.getItem('session_id');
      if (sessionId) {
        config.headers['X-Session-ID'] = sessionId;
      }
    } catch (error) {
      console.error('Error acquiring token:', error);
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => {
    // Store session ID if returned
    if (response.data?.session_id) {
      localStorage.setItem('session_id', response.data.session_id);
    }
    return response;
  },
  (error) => {
    if (error.response) {
      // Handle specific error codes
      switch (error.response.status) {
        case 401:
          // Only redirect to login if not using mock auth
          if (process.env.REACT_APP_USE_MOCK_AUTH !== 'true') {
            msalInstance.logoutRedirect();
            toast.error('Session expired. Please login again.');
          } else {
            toast.error('Authentication error in development mode.');
          }
          break;
        case 403:
          toast.error('You do not have permission to perform this action.');
          break;
        case 404:
          console.warn('Resource not found:', error.config.url);
          break;
        case 429:
          toast.error('Too many requests. Please slow down.');
          break;
        case 500:
          toast.error('Server error. Please try again later.');
          break;
        default:
          if (error.response.data?.message) {
            toast.error(error.response.data.message);
          }
      }
    } else if (error.request) {
      toast.error('Network error. Please check your connection.');
    } else {
      console.error('API Error:', error.message);
    }
    
    return Promise.reject(error);
  }
);

export default api;