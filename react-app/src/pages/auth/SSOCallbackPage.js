/**
 * SSO Callback Handler
 * 
 * This component handles the return from Azure AD after SAML authentication.
 * It processes the token and user data, then redirects to the appropriate page.
 */

import React, { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import {
  Box,
  Container,
  CircularProgress,
  Typography,
  Alert,
  Card,
  CardContent
} from '@mui/material';
import { setUser } from '../../store/slices/authSlice';
import ssoAuthService from '../../services/ssoAuthService';
import toast from 'react-hot-toast';

const SSOCallbackPage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const [searchParams] = useSearchParams();
  
  const [status, setStatus] = useState('processing'); // processing, success, error
  const [errorMessage, setErrorMessage] = useState('');

  useEffect(() => {
    const processCallback = async () => {
      try {
        // Get token and user data from URL parameters
        // These will be provided by the PHP backend after SAML validation
        const token = searchParams.get('token');
        const error = searchParams.get('error');
        const errorDescription = searchParams.get('error_description');
        
        // Check for error first
        if (error) {
          setStatus('error');
          setErrorMessage(errorDescription || 'SSO authentication failed');
          toast.error('SSO login failed');
          
          // Redirect to login after 3 seconds
          setTimeout(() => navigate('/login'), 3000);
          return;
        }
        
        // Validate token exists
        if (!token) {
          setStatus('error');
          setErrorMessage('No authentication token received');
          toast.error('SSO login failed - no token');
          setTimeout(() => navigate('/login'), 3000);
          return;
        }
        
        // Parse user data from URL (base64 encoded JSON)
        const userDataParam = searchParams.get('user');
        if (!userDataParam) {
          setStatus('error');
          setErrorMessage('No user data received');
          toast.error('SSO login failed - no user data');
          setTimeout(() => navigate('/login'), 3000);
          return;
        }
        
        // Decode user data
        let userData;
        try {
          const decodedData = atob(userDataParam);
          userData = JSON.parse(decodedData);
        } catch (err) {
          console.error('Error parsing user data:', err);
          setStatus('error');
          setErrorMessage('Invalid user data format');
          toast.error('SSO login failed - invalid data');
          setTimeout(() => navigate('/login'), 3000);
          return;
        }
        
        // Process the callback with the SSO auth service
        const result = await ssoAuthService.handleSAMLCallback(token, userData);
        
        // Update Redux store
        dispatch(setUser({
          user: result.user,
          authMethod: 'sso'
        }));
        
        setStatus('success');
        toast.success(`Welcome back, ${result.user.name}!`);
        
        // Redirect to the intended page or home
        setTimeout(() => {
          navigate(result.returnUrl);
        }, 1000);
        
      } catch (err) {
        console.error('SSO callback error:', err);
        setStatus('error');
        setErrorMessage(err.message || 'An unexpected error occurred');
        toast.error('SSO login failed');
        setTimeout(() => navigate('/login'), 3000);
      }
    };
    
    processCallback();
  }, [searchParams, navigate, dispatch]);

  return (
    <Container maxWidth="sm">
      <Box sx={{ mt: 8, mb: 4 }}>
        <Card elevation={3}>
          <CardContent sx={{ p: 4, textAlign: 'center' }}>
            {status === 'processing' && (
              <>
                <CircularProgress size={60} sx={{ mb: 3 }} />
                <Typography variant="h5" gutterBottom>
                  Completing SSO Login...
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Please wait while we verify your credentials
                </Typography>
              </>
            )}
            
            {status === 'success' && (
              <>
                <Box sx={{ fontSize: 60, mb: 2 }}>✓</Box>
                <Typography variant="h5" gutterBottom color="success.main">
                  Login Successful!
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Redirecting you now...
                </Typography>
              </>
            )}
            
            {status === 'error' && (
              <>
                <Box sx={{ fontSize: 60, mb: 2, color: 'error.main' }}>✕</Box>
                <Typography variant="h5" gutterBottom color="error.main">
                  Login Failed
                </Typography>
                <Alert severity="error" sx={{ mt: 2, textAlign: 'left' }}>
                  {errorMessage}
                </Alert>
                <Typography variant="body2" color="text.secondary" sx={{ mt: 2 }}>
                  Redirecting to login page...
                </Typography>
              </>
            )}
          </CardContent>
        </Card>
      </Box>
    </Container>
  );
};

export default SSOCallbackPage;
