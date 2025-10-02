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
    // Check if we're using mock authentication
    if (process.env.REACT_APP_USE_MOCK_AUTH === 'true') {
      // Set mock user data
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
    } else {
      // Use real Azure AD authentication
      if (accounts.length > 0) {
        const account = accounts[0];
        dispatch(setUser({ 
          user: {
            name: account.name,
            email: account.username,
            firstName: account.name?.split(' ')[0],
            lastName: account.name?.split(' ')[1]
          }
        }));
        setIsAuthenticated(true);
      }
      setLoading(false);
    }
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