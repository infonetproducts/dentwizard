/**
 * Enhanced Login Page with SSO Support
 * 
 * Supports three authentication methods:
 * 1. Standard (email/password) - existing PHP users
 * 2. SSO (SAML 2.0) - DentWizard employees via Azure AD
 * 3. Development mode - for testing
 */

import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import {
  Box,
  Button,
  Card,
  CardContent,
  Container,
  Typography,
  TextField,
  useTheme,
  useMediaQuery,
  CircularProgress,
  Alert,
  Divider,
  Stack
} from '@mui/material';
import { motion } from 'framer-motion';
import MicrosoftIcon from '@mui/icons-material/Microsoft';
import toast from 'react-hot-toast';
import dentwizardLogo from '../images/dentwizard.png';
import api from '../services/api';
import ssoAuthService from '../services/ssoAuthService';
import { setUser } from '../store/slices/authSlice';

const LoginPageSSO = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [authType, setAuthType] = useState(null); // null, 'standard', 'sso'
  const [showPassword, setShowPassword] = useState(false);

  // Development mode - bypass auth
  const isDevelopment = process.env.REACT_APP_USE_MOCK_AUTH === 'true';

  // Detect authentication type when user enters email
  const handleEmailBlur = async () => {
    if (!email || email.length < 5) return;
    
    setLoading(true);
    setError(null);
    
    try {
      const result = await ssoAuthService.detectAuthType(email);
      
      setAuthType(result.authType);
      setShowPassword(result.authType === 'standard');
      
      if (!result.userExists) {
        setError(result.message || 'User not found. Please check your email address.');
      } else if (result.requiresSSO) {
        // Show info that this user requires SSO
        toast.info('Please use Single Sign-On to continue');
      }
    } catch (err) {
      console.error('Error detecting auth type:', err);
      // Default to standard auth
      setAuthType('standard');
      setShowPassword(true);
    } finally {
      setLoading(false);
    }
  };

  // Handle standard (email/password) login
  const handleStandardLogin = async (e) => {
    e?.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await api.post('/auth/login-token.php', {
        email,
        password
      });

      if (response.data.error) {
        setError(response.data.error);
        toast.error(response.data.error);
        return;
      }

      if (response.data.success) {
        // Store auth data (existing pattern)
        localStorage.setItem('authToken', response.data.token);
        localStorage.setItem('authMethod', 'standard');
        localStorage.setItem('user', JSON.stringify(response.data.user));
        localStorage.setItem('userId', response.data.user.id);
        localStorage.setItem('userName', response.data.user.name);
        localStorage.setItem('userEmail', response.data.user.email);
        
        // Update Redux store
        dispatch(setUser({
          user: response.data.user,
          authMethod: 'standard'
        }));
        
        toast.success('Login successful!');
        navigate('/');
      } else {
        setError('Login failed. Please check your credentials.');
        toast.error('Login failed. Please check your credentials.');
      }
    } catch (err) {
      const errorMsg = err.response?.data?.error || 'Login failed. Please try again.';
      setError(errorMsg);
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  // Handle SSO login
  const handleSSOLogin = () => {
    setLoading(true);
    try {
      // Store return URL for after SSO
      ssoAuthService.setReturnUrl(window.location.pathname || '/');
      
      // Redirect to SSO login (handled by PHP backend)
      ssoAuthService.initiateSSO();
    } catch (err) {
      setError('Unable to initiate SSO login. Please try again.');
      toast.error('SSO login failed');
      setLoading(false);
    }
  };

  // Development quick login
  const handleDevLogin = () => {
    const devUser = {
      id: 19346,
      email: 'joseph.lorenzo@dentwizard.com',
      name: 'Joe Lorenzo',
      budget: {
        limit: 165.00,
        balance: 165.00
      }
    };
    
    localStorage.setItem('authMethod', 'dev');
    localStorage.setItem('user', JSON.stringify(devUser));
    localStorage.setItem('userId', devUser.id);
    localStorage.setItem('userName', devUser.name);
    localStorage.setItem('userEmail', devUser.email);
    
    dispatch(setUser({
      user: devUser,
      authMethod: 'dev'
    }));
    
    toast.success('Development login successful!');
    navigate('/');
  };

  return (
    <Container maxWidth="sm">
      <Box sx={{ mt: 8, mb: 4 }}>
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
        >
          <Card elevation={3}>
            <CardContent sx={{ p: 4 }}>
              {/* Logo */}
              <Box sx={{ textAlign: 'center', mb: 4 }}>
                <img 
                  src={dentwizardLogo} 
                  alt="DentWizard" 
                  style={{ height: 60, marginBottom: 16 }}
                />
                <Typography variant="h4" gutterBottom>
                  Sign In
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {authType === null ? 'Enter your email to continue' : 
                   authType === 'sso' ? 'Sign in with your DentWizard account' :
                   'Enter your password to continue'}
                </Typography>
              </Box>

              {/* Error Alert */}
              {error && (
                <Alert severity="error" sx={{ mb: 2 }} onClose={() => setError(null)}>
                  {error}
                </Alert>
              )}

              {/* Login Form */}
              <Box component="form" onSubmit={handleStandardLogin}>
                {/* Email Field - Always shown */}
                <TextField
                  fullWidth
                  label="Email Address"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  onBlur={handleEmailBlur}
                  margin="normal"
                  required
                  autoComplete="email"
                  autoFocus
                  disabled={loading}
                />

                {/* Password Field - Only for standard auth */}
                {showPassword && authType === 'standard' && (
                  <>
                    <TextField
                      fullWidth
                      label="Password"
                      type="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      margin="normal"
                      required
                      autoComplete="current-password"
                      disabled={loading}
                    />
                    
                    <Button
                      type="submit"
                      fullWidth
                      variant="contained"
                      size="large"
                      sx={{ mt: 3, mb: 2 }}
                      disabled={loading || !email || !password}
                    >
                      {loading ? <CircularProgress size={24} /> : 'Sign In'}
                    </Button>
                  </>
                )}

                {/* SSO Button - Only for SSO users */}
                {authType === 'sso' && (
                  <>
                    <Alert severity="info" sx={{ mt: 2, mb: 2 }}>
                      Your @dentwizard.com email requires Single Sign-On
                    </Alert>
                    
                    <Button
                      fullWidth
                      variant="contained"
                      size="large"
                      startIcon={<MicrosoftIcon />}
                      onClick={handleSSOLogin}
                      disabled={loading}
                      sx={{
                        mt: 2,
                        bgcolor: '#0078d4',
                        '&:hover': { bgcolor: '#106ebe' }
                      }}
                    >
                      {loading ? <CircularProgress size={24} /> : 'Sign in with Microsoft'}
                    </Button>
                    
                    <Button
                      fullWidth
                      variant="text"
                      size="small"
                      onClick={() => {
                        setAuthType(null);
                        setEmail('');
                        setShowPassword(false);
                      }}
                      sx={{ mt: 1 }}
                    >
                      Use a different email
                    </Button>
                  </>
                )}
              </Box>

              {/* Development Mode Quick Login */}
              {isDevelopment && (
                <>
                  <Divider sx={{ my: 3 }}>Development Mode</Divider>
                  <Stack spacing={1}>
                    <Button
                      fullWidth
                      variant="outlined"
                      onClick={handleDevLogin}
                      disabled={loading}
                    >
                      Quick Login as Joe Lorenzo
                    </Button>
                    <Typography variant="caption" color="text.secondary" align="center">
                      Development mode active - Authentication bypassed
                    </Typography>
                  </Stack>
                </>
              )}
            </CardContent>
          </Card>
        </motion.div>
      </Box>
    </Container>
  );
};

export default LoginPageSSO;
