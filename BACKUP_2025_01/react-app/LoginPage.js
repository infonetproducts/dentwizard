import React, { useEffect } from 'react';
import { useMsal } from '@azure/msal-react';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  Button,
  Card,
  CardContent,
  Container,
  Typography,
  Stack,
  useTheme,
  useMediaQuery,
  CircularProgress,
  Alert,
  Divider
} from '@mui/material';
import { motion } from 'framer-motion';
import MicrosoftIcon from '@mui/icons-material/Microsoft';
import ShoppingBagIcon from '@mui/icons-material/ShoppingBag';
import { loginRequest } from '../authConfig';
import toast from 'react-hot-toast';
import dentwizardLogo from '../images/dentwizard.png';

const LoginPage = () => {
  const { instance, accounts } = useMsal();
  const navigate = useNavigate();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const [loading, setLoading] = React.useState(false);
  const [error, setError] = React.useState(null);

  // Redirect if already authenticated
  useEffect(() => {
    // Skip Azure AD check if using mock auth
    if (process.env.REACT_APP_USE_MOCK_AUTH === 'true') {
      // Optionally auto-login for development
      // navigate('/');
      return;
    }
    
    if (accounts.length > 0) {
      navigate('/');
    }
  }, [accounts, navigate]);
  
  const handleLogin = async () => {
    setLoading(true);
    setError(null);
    
    try {
      // Check if we're using mock authentication
      if (process.env.REACT_APP_USE_MOCK_AUTH === 'true') {
        // Simulate login delay
        await new Promise(resolve => setTimeout(resolve, 1000));
        toast.success('Welcome to DentWizard! (Development Mode)');
        navigate('/');
      } else {
        // Use real Azure AD authentication
        await instance.loginPopup(loginRequest);
        toast.success('Successfully logged in!');
        navigate('/');
      }
    } catch (err) {
      console.error('Login error:', err);
      setError('Failed to sign in. Please try again or contact support.');
      toast.error('Login failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleRedirectLogin = async () => {
    setLoading(true);
    try {
      await instance.loginRedirect(loginRequest);
    } catch (err) {
      console.error('Login redirect error:', err);
      setError('Failed to redirect to login. Please try again.');
    }
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        p: 2,
      }}
    >
      <Container maxWidth="sm">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
        >
          <Card
            sx={{
              p: isMobile ? 2 : 4,
              borderRadius: 3,
              boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
            }}
          >
            <CardContent>
              <Stack spacing={3} alignItems="center">
                <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2 }}>
                  <img 
                    src={dentwizardLogo} 
                    alt="DentWizard" 
                    style={{ 
                      height: isMobile ? '70px' : '90px',
                      width: 'auto'
                    }} 
                  />
                  <Typography
                    variant={isMobile ? 'h6' : 'h5'}
                    component="h2"
                    sx={{ fontWeight: 600, color: theme.palette.text.primary }}
                  >
                    Corporate Apparel
                  </Typography>
                </Box>

                <Typography
                  variant="body1"
                  color="text.secondary"
                  align="center"
                  sx={{ mb: 2 }}
                >
                  Sign in with your corporate account to access DentWizard apparel
                </Typography>
                {error && (
                  <Alert severity="error" sx={{ width: '100%' }}>
                    {error}
                  </Alert>
                )}

                <Divider sx={{ width: '100%' }}>
                  <Typography variant="body2" color="text.secondary">
                    Sign in with
                  </Typography>
                </Divider>

                <Button
                  fullWidth
                  variant="contained"
                  size="large"
                  onClick={handleLogin}
                  disabled={loading}
                  startIcon={loading ? <CircularProgress size={20} /> : <MicrosoftIcon />}
                  sx={{
                    py: 1.5,
                    fontSize: '1.1rem',
                    background: '#0078d4',
                    '&:hover': {
                      background: '#106ebe',
                    },
                  }}
                >
                  {loading ? 'Signing in...' : 'Sign in with Microsoft'}
                </Button>

                <Typography
                  variant="caption"
                  color="text.secondary"
                  align="center"
                  sx={{ mt: 2 }}
                >
                  Use your DentWizard corporate account
                </Typography>

                {/* Alternative login for popup blocked */}
                {error && error.includes('popup') && (
                  <Button
                    fullWidth
                    variant="outlined"
                    size="large"
                    onClick={handleRedirectLogin}
                    sx={{ mt: 1 }}
                  >
                    Try redirect login instead
                  </Button>
                )}

                {/* Dev mode bypass for testing */}
                {process.env.REACT_APP_DEV_MODE === 'true' && (
                  <Box sx={{ mt: 3, pt: 2, borderTop: '1px solid #e0e0e0' }}>
                    <Typography variant="caption" color="text.secondary" display="block" align="center" sx={{ mb: 1 }}>
                      Development Mode
                    </Typography>
                    <Button
                      fullWidth
                      variant="outlined"
                      onClick={() => navigate('/')}
                      size="small"
                    >
                      Skip Login (Dev Only)
                    </Button>
                  </Box>
                )}
              </Stack>
            </CardContent>
          </Card>
        </motion.div>
      </Container>
    </Box>
  );
};

export default LoginPage;
