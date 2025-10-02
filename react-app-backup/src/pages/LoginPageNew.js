import React, { useState } from 'react';
import {
  Box,
  Container,
  Paper,
  TextField,
  Button,
  Typography,
  Alert,
  Divider,
  CircularProgress
} from '@mui/material';
import { Microsoft } from '@mui/icons-material';
import { useNavigate } from 'react-router-dom';
import { useMsal } from '@azure/msal-react';
import api from '../services/api';

function LoginPage() {
  const navigate = useNavigate();
  const { instance } = useMsal();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [authType, setAuthType] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  // Check authentication type when email is entered
  const checkAuthType = async () => {
    if (!email) return;
    
    try {
      const response = await api.post('/auth/check-type.php', { email });
      setAuthType(response.data.auth_type);
      
      if (!response.data.exists) {
        setError('User not found. Please check your email.');
      } else {
        setError('');
      }
    } catch (err) {
      console.error('Error checking auth type:', err);
    }
  };

  // Handle standard login
  const handleStandardLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const response = await api.post('/auth/login.php', {
        email,
        password
      });

      if (response.data.success) {
        // Store token and user data
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        
        // Update Redux store or context if you have one
        // dispatch(setUser(response.data.user));
        
        // Redirect to profile or home
        navigate('/profile');
      }
    } catch (err) {
      setError(err.response?.data?.error || 'Login failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  // Handle SSO login
  const handleSSOLogin = async () => {
    setLoading(true);
    try {
      const result = await instance.loginPopup({
        scopes: ["user.read"]
      });
      
      // Send token to backend to verify and create session
      const response = await api.post('/auth/verify-sso.php', {
        token: result.accessToken,
        email: result.account.username
      });
      
      if (response.data.success) {
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        navigate('/profile');
      }
    } catch (err) {
      setError('SSO login failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container maxWidth="sm">
      <Box sx={{ mt: 8, mb: 4 }}>
        <Paper elevation={3} sx={{ p: 4 }}>
          <Typography variant="h4" align="center" gutterBottom>
            Sign In
          </Typography>
          
          <Typography variant="body2" align="center" color="text.secondary" sx={{ mb: 3 }}>
            Enter your email to continue
          </Typography>

          {error && (
            <Alert severity="error" sx={{ mb: 2 }}>
              {error}
            </Alert>
          )}

          <Box component="form" onSubmit={handleStandardLogin}>
            <TextField
              fullWidth
              label="Email Address"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              onBlur={checkAuthType}
              margin="normal"
              required
              autoComplete="email"
              autoFocus
            />

            {authType === 'standard' && (
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
                />
                
                <Button
                  type="submit"
                  fullWidth
                  variant="contained"
                  sx={{ mt: 3, mb: 2 }}
                  disabled={loading || !email || !password}
                >
                  {loading ? <CircularProgress size={24} /> : 'Sign In'}
                </Button>
              </>
            )}

            {authType === 'sso' && (
              <>
                <Box sx={{ my: 3 }}>
                  <Divider>Use Single Sign-On</Divider>
                </Box>
                
                <Button
                  fullWidth
                  variant="contained"
                  startIcon={<Microsoft />}
                  onClick={handleSSOLogin}
                  disabled={loading}
                  sx={{
                    bgcolor: '#0078d4',
                    '&:hover': { bgcolor: '#106ebe' }
                  }}
                >
                  {loading ? <CircularProgress size={24} /> : 'Sign in with Microsoft'}
                </Button>
              </>
            )}

            {authType === 'both' && (
              <>
                <TextField
                  fullWidth
                  label="Password"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  margin="normal"
                  autoComplete="current-password"
                />
                
                <Button
                  type="submit"
                  fullWidth
                  variant="contained"
                  sx={{ mt: 3, mb: 2 }}
                  disabled={loading || !email || !password}
                >
                  {loading ? <CircularProgress size={24} /> : 'Sign In with Password'}
                </Button>
                
                <Divider sx={{ my: 2 }}>OR</Divider>
                
                <Button
                  fullWidth
                  variant="outlined"
                  startIcon={<Microsoft />}
                  onClick={handleSSOLogin}
                  disabled={loading}
                >
                  Sign in with Microsoft
                </Button>
              </>
            )}
          </Box>

          {/* FOR DEVELOPMENT - Remove in production */}
          <Box sx={{ mt: 4, pt: 2, borderTop: '1px dashed #ccc' }}>
            <Typography variant="caption" color="text.secondary">
              Development Quick Login:
            </Typography>
            <Button
              size="small"
              onClick={() => {
                setEmail('joseph.lorenzo@dentwizard.com');
                setPassword('test123'); // You'll need to set Joe's password
                setAuthType('standard');
              }}
            >
              Use Joe Lorenzo
            </Button>
          </Box>
        </Paper>
      </Box>
    </Container>
  );
}

export default LoginPage;