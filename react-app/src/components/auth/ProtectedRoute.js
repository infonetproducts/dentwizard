import React, { useEffect } from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useMsal } from '@azure/msal-react';
import { useDispatch } from 'react-redux';
import { setUser } from '../../store/slices/authSlice';
import { CircularProgress, Box } from '@mui/material';

const ProtectedRoute = () => {
  const { instance, accounts } = useMsal();
  const dispatch = useDispatch();
  const [loading, setLoading] = React.useState(true);
  const [isAuthenticated, setIsAuthenticated] = React.useState(false);

  useEffect(() => {
    // Check authentication in order of priority
    
    // 1. Check if we're using mock authentication
    if (process.env.REACT_APP_USE_MOCK_AUTH === 'true') {
      const mockUser = {
        name: 'John Demo',
        email: 'john.demo@dentwizard.com',
        firstName: 'John',
        lastName: 'Demo',
        department: 'IT Department',
        employeeId: 'DW12345'
      };
      
      dispatch(setUser({ 
        user: mockUser,
        budget: {
          allocated: 500.00,
          used: 125.50,
          remaining: 374.50
        }
      }));
      
      setIsAuthenticated(true);
      setLoading(false);
      return;
    }
    
    // 2. Check for standard token-based authentication (email/password)
    const authToken = localStorage.getItem('authToken');
    const authMethod = localStorage.getItem('authMethod');
    const userStr = localStorage.getItem('user');
    
    if (authToken && authMethod === 'standard' && userStr) {
      try {
        const user = JSON.parse(userStr);
        dispatch(setUser({ 
          user: user,
          authMethod: 'standard'
        }));
        setIsAuthenticated(true);
        setLoading(false);
        return;
      } catch (err) {
        console.error('Error parsing user from localStorage:', err);
      }
    }
    
    // 3. Check for development mode authentication
    if (authMethod === 'dev' && userStr) {
      try {
        const user = JSON.parse(userStr);
        dispatch(setUser({ 
          user: user,
          authMethod: 'dev'
        }));
        setIsAuthenticated(true);
        setLoading(false);
        return;
      } catch (err) {
        console.error('Error parsing dev user from localStorage:', err);
      }
    }
    
    // 4. Check for Azure AD SSO authentication
    if (accounts.length > 0) {
      const account = accounts[0];
      dispatch(setUser({ 
        user: {
          name: account.name,
          email: account.username,
          firstName: account.name?.split(' ')[0],
          lastName: account.name?.split(' ')[1]
        },
        authMethod: 'sso'
      }));
      setIsAuthenticated(true);
      setLoading(false);
      return;
    }
    
    // No authentication found
    setIsAuthenticated(false);
    setLoading(false);
  }, [accounts, dispatch]);

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        <CircularProgress />
      </Box>
    );
  }

  if (!isAuthenticated && process.env.REACT_APP_USE_MOCK_AUTH !== 'true') {
    return <Navigate to="/login" replace />;
  }

  return <Outlet />;
};

export default ProtectedRoute;