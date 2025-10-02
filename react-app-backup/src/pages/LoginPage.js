import React, { useEffect, useState } from 'react';
import { useMsal } from '@azure/msal-react';
import { useNavigate } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import {
  Box,
  Button,
  Card,
  CardContent,
  Container,
  Typography,
  Stack,
  TextField,
  useTheme,
  useMediaQuery,
  CircularProgress,
  Alert,
  Divider
} from '@mui/material';
import { motion } from 'framer-motion';
import MicrosoftIcon from '@mui/icons-material/Microsoft';
import { loginRequest } from '../authConfig';
import toast from 'react-hot-toast';
import dentwizardLogo from '../images/dentwizard.png';
import api from '../services/api';
import { setUser } from '../store/slices/authSlice';

const LoginPage = () => {
  const { instance, accounts } = useMsal();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [authType, setAuthType] = useState('standard'); // Default to standard to show both fields
  const [emailChecked, setEmailChecked] = useState(true); // Default to true to show password field

  // Development mode - bypass auth
  const isDevelopment = process.env.REACT_APP_USE_MOCK_AUTH === 'true';

  // Check auth type when email is entered
  const checkAuthType = async (emailValue) => {
    if (!emailValue || emailValue.length < 3) return;
    
    try {
      const response = await api.post('/auth/check-type.php', { email: emailValue });
      setAuthType(response.data.auth_type);
      setEmailChecked(true);
      
      if (!response.data.exists && !isDevelopment) {
        setError('User not found. Please check your email address.');
      }
    } catch (err) {
      console.error('Error checking auth type:', err);
      // Default to standard for now
      setAuthType('standard');
      setEmailChecked(true);
    }
  };

  // Handle standard login
  const handleStandardLogin = async (e) => {
    e?.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await api.post('/auth/login-token.php', {
        email,
        password
      });

      // Check if response contains an error
      if (response.data.error) {
        setError(response.data.error);
        toast.error(response.data.error);
        setLoading(false);
        return;
      }

      // Check for success
      if (response.data.success) {
        // Store auth data
        localStorage.setItem('authToken', response.data.token);
        localStorage.setItem('authMethod', 'standard');
        localStorage.setItem('user', JSON.stringify(response.data.user));
        // Add missing fields that other components expect
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
        // Handle unexpected response
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
  const handleSSOLogin = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const result = await instance.loginPopup(loginRequest);
      
      // For now, just use the Azure AD account
      // Later, verify with backend
      const user = {
        id: result.account.localAccountId,
        email: result.account.username,
        name: result.account.name,
      };
      
      localStorage.setItem('authMethod', 'sso');
      localStorage.setItem('user', JSON.stringify(user));
      // Add missing fields
      localStorage.setItem('userId', user.id);
      localStorage.setItem('userName', user.name);
      localStorage.setItem('userEmail', user.email);
      
      dispatch(setUser({
        user,
        authMethod: 'sso'
      }));
      
      toast.success('Login successful!');
      navigate('/');
    } catch (err) {
      setError('SSO login failed. Please try again.');
      toast.error('SSO login failed');
    } finally {
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
    // Add missing fields
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
                  Enter your email to continue
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
                <TextField
                  fullWidth
                  label="Email Address"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  onBlur={(e) => checkAuthType(e.target.value)}
                  margin="normal"
                  required
                  autoComplete="email"
                  autoFocus
                  disabled={loading}
                />

                {/* Show appropriate auth method after email is entered */}
                {emailChecked && authType === 'standard' && (
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

                {emailChecked && authType === 'sso' && (
                  <>
                    <Alert severity="info" sx={{ mt: 2, mb: 2 }}>
                      @dentwizard.com emails use Microsoft Single Sign-On
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

export default LoginPage;