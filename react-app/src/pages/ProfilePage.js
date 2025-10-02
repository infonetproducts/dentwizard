import React, { useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { useNavigate, useLocation } from 'react-router-dom';
import {
  Box,
  Container,
  Grid,
  Paper,
  Typography,
  Avatar,
  Button,
  TextField,
  Tab,
  Tabs,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
  ListItemSecondaryAction,
  IconButton,
  Chip,
  Divider,
  Card,
  CardContent,
  CardActions,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Alert,
  Skeleton,
  useTheme,
  useMediaQuery,
  Badge,
  Switch,
  FormControlLabel,
  Stack,
  CircularProgress
} from '@mui/material';
import {
  Person,
  Email,
  Phone,
  LocationOn,
  ShoppingBag,
  Settings,
  Edit,
  Delete,
  Add,
  LocalShipping,
  Receipt,
  Notifications,
  Security,
  CreditCard,
  Business,
  Save,
  Cancel,
  CheckCircle,
  Error,
  Schedule,
  MoreVert,
  Logout
} from '@mui/icons-material';
import { useMsal } from '@azure/msal-react';
import { setUser } from '../store/slices/authSlice';
import { fetchUserProfile } from '../store/slices/profileSlice';
import api from '../services/api';

function TabPanel({ children, value, index, ...other }) {
  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`profile-tabpanel-${index}`}
      aria-labelledby={`profile-tab-${index}`}
      {...other}
    >
      {value === index && <Box sx={{ p: { xs: 2, md: 3 } }}>{children}</Box>}
    </div>
  );
}

function ProfilePage() {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useLocation();
  const { instance } = useMsal();
  
  // Get profile data from profile slice
  const { user: profileUser, budget, shippingAddress, loading: profileLoading, error: profileError } = useSelector(state => state.profile);
  const authUser = useSelector(state => state.auth.user);
  
  const [tabValue, setTabValue] = useState(0);
  const [loading, setLoading] = useState(false);
  const [editMode, setEditMode] = useState(false);
  const [orders, setOrders] = useState([]);
  const [addresses, setAddresses] = useState([]);
  const [notifications, setNotifications] = useState({
    orderUpdates: true,
    promotions: false,
    newsletter: true
  });
  
  // Fetch profile data on component mount
  useEffect(() => {
    dispatch(fetchUserProfile());
  }, [dispatch]);
  
  // Handle navigation state to set active tab
  useEffect(() => {
    if (location.state?.activeTab !== undefined) {
      setTabValue(location.state.activeTab);
    }
  }, [location.state]);
  
  // Edit states - use profile data if available, otherwise use auth user
  const [profileData, setProfileData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    department: ''
  });
  
  // Update profileData when profile loads
  useEffect(() => {
    if (profileUser) {
      const nameParts = (profileUser.name || '').split(' ');
      setProfileData({
        firstName: nameParts[0] || '',
        lastName: nameParts.slice(1).join(' ') || '',
        email: profileUser.email || '',
        phone: profileUser.phone || '',
        department: profileUser.department || '',
        employeeId: profileUser.employeeType || profileUser.id || ''
      });
    }
  }, [profileUser]);
  
  const [addressDialog, setAddressDialog] = useState({
    open: false,
    data: null
  });
  
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    // Enable fetching user data now that endpoints exist
    fetchUserData();
  }, []);

  const fetchUserData = async () => {
    setLoading(true);
    try {
      // Try to fetch addresses (orders endpoint may not exist yet)
      try {
        const addressesRes = await api.get('/user/addresses.php');
        setAddresses(addressesRes.data.data || []);
      } catch (error) {
        console.log('Could not fetch addresses');
        setAddresses([]);
      }
      
      // Try to fetch orders separately
      try {
        console.log('Fetching orders from /orders/my-orders.php');
        const ordersRes = await api.get('/orders/my-orders.php');
        console.log('Orders response:', ordersRes.data);
        setOrders(ordersRes.data.orders || []);
      } catch (error) {
        console.error('Could not fetch orders:', error);
        // Try alternative endpoint
        try {
          const altOrdersRes = await api.get('/user/orders');
          console.log('Alternative orders response:', altOrdersRes.data);
          setOrders(altOrdersRes.data.orders || altOrdersRes.data || []);
        } catch (altError) {
          console.error('Alternative endpoint also failed:', altError);
          setOrders([]);
        }
      }
    } catch (error) {
      console.error('Failed to fetch user data:', error);
      setError('Failed to load profile data');
    } finally {
      setLoading(false);
    }
  };

  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };

  const handleProfileUpdate = async () => {
    try {
      await api.put('/user/profile', profileData);
      setEditMode(false);
      setSuccess('Profile updated successfully');
      // Update Redux store
      dispatch(setUser({ user: { ...authUser, ...profileData } }));
    } catch (error) {
      setError('Failed to update profile');
    }
  };

  const handleAddressSubmit = async () => {
    try {
      if (addressDialog.data?.id) {
        await api.put(`/user/addresses.php/${addressDialog.data.id}`, addressDialog.data);
        setSuccess('Address updated successfully');
      } else {
        await api.post('/user/addresses.php', addressDialog.data);
        setSuccess('Address added successfully');
      }
      setAddressDialog({ open: false, data: null });
      fetchUserData();
    } catch (error) {
      setError('Failed to save address');
    }
  };

  const handleAddressDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this address?')) {
      try {
        await api.delete(`/user/addresses.php/${id}`);
        setSuccess('Address deleted successfully');
        fetchUserData();
      } catch (error) {
        setError('Failed to delete address');
      }
    }
  };

  const handleNotificationChange = async (setting) => {
    try {
      const updatedSettings = { ...notifications, ...setting };
      await api.put('/user/notifications', updatedSettings);
      setNotifications(updatedSettings);
      setSuccess('Notification preferences updated');
    } catch (error) {
      setError('Failed to update notifications');
    }
  };

  const handleLogout = async () => {
    try {
      // Call logout API to clear server session
      await fetch('/lg/API/v1/auth/logout.php', {
        method: 'POST',
        credentials: 'include' // Include cookies for session
      });
    } catch (error) {
      console.error('Logout API error:', error);
      // Continue with logout even if API fails
    }
    
    // Clear local storage
    localStorage.removeItem('authToken');
    localStorage.removeItem('userId');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userName');
    
    // Clear Redux store (safely)
    try {
      dispatch(setUser(null));
    } catch (error) {
      console.error('Redux dispatch error:', error);
    }
    
    // Check if user was logged in via Azure AD
    const accounts = instance.getAllAccounts();
    if (accounts && accounts.length > 0) {
      // Azure AD logout
      instance.logoutRedirect({
        postLogoutRedirectUri: '/login'
      });
    } else {
      // Standard logout - redirect to login page
      navigate('/login');
    }
  };

  const getOrderStatusColor = (status) => {
    const statusColors = {
      pending: 'warning',
      processing: 'info',
      shipped: 'primary',
      delivered: 'success',
      cancelled: 'error'
    };
    return statusColors[status] || 'default';
  };

  const getOrderStatusIcon = (status) => {
    const statusIcons = {
      pending: <Schedule />,
      processing: <Settings />,
      shipped: <LocalShipping />,
      delivered: <CheckCircle />,
      cancelled: <Error />
    };
    return statusIcons[status] || <Receipt />;
  };

  const profileTabs = [
    { label: 'Profile', icon: <Person /> },
    { label: 'Orders', icon: <ShoppingBag /> },
    { label: 'Addresses', icon: <LocationOn /> },
    { label: 'Settings', icon: <Settings /> }
  ];

  return (
    <Container maxWidth="lg" sx={{ py: { xs: 2, md: 4 } }}>
      {/* Show loading state while fetching profile */}
      {profileLoading && (
        <Box sx={{ display: 'flex', justifyContent: 'center', p: 4 }}>
          <CircularProgress />
        </Box>
      )}
      
      {/* Show error if profile failed to load */}
      {profileError && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {profileError}
        </Alert>
      )}
      
      {/* Profile Header */}
      <Paper 
        sx={{ 
          p: { xs: 2, md: 3 },
          mb: 3,
          background: `linear-gradient(135deg, ${theme.palette.primary.main} 0%, ${theme.palette.primary.dark} 100%)`,
          color: 'white',
          position: 'relative',
          overflow: 'hidden'
        }}
      >
        <Grid container spacing={2} alignItems="center">
          <Grid item xs={12} md="auto">
            <Avatar
              sx={{
                width: { xs: 80, md: 100 },
                height: { xs: 80, md: 100 },
                bgcolor: 'white',
                color: 'primary.main',
                fontSize: { xs: 32, md: 40 },
                mx: { xs: 'auto', md: 0 }
              }}
            >
              {profileUser?.name ? profileUser.name.split(' ').map(n => n[0]).join('') : 'JD'}
            </Avatar>
          </Grid>
          <Grid item xs={12} md>
            <Typography variant={isMobile ? 'h5' : 'h4'} gutterBottom align={isMobile ? 'center' : 'left'}>
              {profileUser?.name || 'John Demo'}
            </Typography>
            <Stack 
              direction={isMobile ? 'column' : 'row'} 
              spacing={2}
              alignItems={isMobile ? 'center' : 'flex-start'}
            >
              <Chip
                icon={<Email sx={{ color: 'white !important' }} />}
                label={profileUser?.email || 'john.demo@dentwizard.com'}
                variant="outlined"
                sx={{ 
                  color: 'white', 
                  borderColor: 'rgba(255,255,255,0.5)',
                  '& .MuiChip-icon': {
                    color: 'white'
                  }
                }}
              />
              {profileUser?.department && (
                <Chip
                  icon={<Business />}
                  label={profileUser.department}
                  variant="outlined"
                  sx={{ color: 'white', borderColor: 'rgba(255,255,255,0.5)' }}
                />
              )}
            </Stack>
          </Grid>
          <Grid item xs={12} md="auto">
            <Button
              variant="contained"
              color="secondary"
              onClick={handleLogout}
              startIcon={<Logout />}
              fullWidth={isMobile}
            >
              Sign Out
            </Button>
          </Grid>
        </Grid>
      </Paper>

      {/* Alerts */}
      {success && (
        <Alert severity="success" onClose={() => setSuccess('')} sx={{ mb: 2 }}>
          {success}
        </Alert>
      )}
      {error && (
        <Alert severity="error" onClose={() => setError('')} sx={{ mb: 2 }}>
          {error}
        </Alert>
      )}

      {/* Tabs */}
      <Paper sx={{ mb: 2 }}>
        <Tabs
          value={tabValue}
          onChange={handleTabChange}
          variant={isMobile ? 'scrollable' : 'fullWidth'}
          scrollButtons="auto"
          allowScrollButtonsMobile
        >
          {profileTabs.map((tab, index) => (
            <Tab
              key={index}
              label={!isMobile && tab.label}
              icon={tab.icon}
              iconPosition="start"
            />
          ))}
        </Tabs>
      </Paper>

      {/* Tab Panels */}
      <Paper>
        {/* Profile Tab */}
        <TabPanel value={tabValue} index={0}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 3 }}>
            <Typography variant="h6">Personal Information</Typography>
            <Button
              startIcon={editMode ? <Cancel /> : <Edit />}
              onClick={() => setEditMode(!editMode)}
            >
              {editMode ? 'Cancel' : 'Edit'}
            </Button>
          </Box>
          
          <Grid container spacing={3}>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="First Name"
                value={profileData.firstName}
                onChange={(e) => setProfileData({ ...profileData, firstName: e.target.value })}
                disabled={!editMode}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Last Name"
                value={profileData.lastName}
                onChange={(e) => setProfileData({ ...profileData, lastName: e.target.value })}
                disabled={!editMode}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Email"
                value={profileData.email}
                disabled
                helperText="Email cannot be changed"
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Phone"
                value={profileData.phone}
                onChange={(e) => setProfileData({ ...profileData, phone: e.target.value })}
                disabled={!editMode}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Department"
                value={profileData.department}
                onChange={(e) => setProfileData({ ...profileData, department: e.target.value })}
                disabled={!editMode}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Employee ID"
                value={profileData.employeeId}
                disabled
                helperText="Employee ID is assigned by your organization"
              />
            </Grid>
          </Grid>
          
          {editMode && (
            <Box sx={{ mt: 3, display: 'flex', gap: 2, justifyContent: 'flex-end' }}>
              <Button onClick={() => setEditMode(false)}>
                Cancel
              </Button>
              <Button
                variant="contained"
                startIcon={<Save />}
                onClick={handleProfileUpdate}
              >
                Save Changes
              </Button>
            </Box>
          )}
        </TabPanel>

        {/* Orders Tab */}
        <TabPanel value={tabValue} index={1}>
          <Typography variant="h6" gutterBottom>
            Order History
          </Typography>
          
          {loading ? (
            <>
              {[1, 2, 3].map(i => (
                <Skeleton key={i} variant="rectangular" height={120} sx={{ mb: 2, borderRadius: 1 }} />
              ))}
            </>
          ) : orders.length === 0 ? (
            <Box sx={{ textAlign: 'center', py: 4 }}>
              <ShoppingBag sx={{ fontSize: 64, color: 'text.secondary', mb: 2 }} />
              <Typography variant="h6" color="text.secondary">
                No orders yet
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1, mb: 3 }}>
                Start shopping to see your orders here
              </Typography>
              <Button variant="contained" onClick={() => navigate('/products')}>
                Browse Products
              </Button>
            </Box>
          ) : (
            <Stack spacing={2}>
              {orders.map((order) => (
                <Card key={order.id}>
                  <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
                      <Box>
                        <Typography variant="h6">
                          Order #{order.order_id || order.id}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          {new Date(order.order_date || order.date).toLocaleString('en-US', {
                            dateStyle: 'medium',
                            timeStyle: 'short'
                          })}
                        </Typography>
                      </Box>
                      <Chip
                        icon={getOrderStatusIcon(order.status)}
                        label={order.status.toUpperCase()}
                        color={getOrderStatusColor(order.status)}
                        size="small"
                      />
                    </Box>
                    
                    <Divider sx={{ my: 2 }} />
                    
                    <List disablePadding>
                      {order.items?.slice(0, 2).map((item, idx) => (
                        <ListItem key={idx} disableGutters>
                          <ListItemText
                            primary={item.name || item.product_name}
                            secondary={
                              <>
                                {`Qty: ${item.quantity}`}
                                {item.size && ` | Size: ${item.size}`}
                                {item.color && ` | Color: ${item.color}`}
                                {item.logo && ` | Logo: ${item.logo}`}
                              </>
                            }
                          />
                          <Typography variant="body2">
                            ${(item.price * item.quantity).toFixed(2)}
                          </Typography>
                        </ListItem>
                      ))}
                      {order.items?.length > 2 && (
                        <Typography variant="body2" color="text.secondary">
                          +{order.items.length - 2} more items
                        </Typography>
                      )}
                    </List>
                    
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 2 }}>
                      <Typography variant="h6">
                        Total: ${order.total?.toFixed(2)}
                      </Typography>
                      <Button size="small" href={`/orders/${order.id}`}>
                        View Details
                      </Button>
                    </Box>
                  </CardContent>
                </Card>
              ))}
            </Stack>
          )}
        </TabPanel>

        {/* Addresses Tab */}
        <TabPanel value={tabValue} index={2}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 3 }}>
            <Typography variant="h6">Saved Addresses</Typography>
            <Button
              startIcon={<Add />}
              onClick={() => setAddressDialog({ 
                open: true, 
                data: { 
                  label: '',
                  address: '',
                  address2: '',
                  city: '',
                  state: '',
                  zipCode: '',
                  isDefault: false 
                }
              })}
            >
              Add Address
            </Button>
          </Box>
          
          {addresses.length === 0 ? (
            <Box sx={{ textAlign: 'center', py: 4 }}>
              <LocationOn sx={{ fontSize: 64, color: 'text.secondary', mb: 2 }} />
              <Typography variant="h6" color="text.secondary">
                No saved addresses
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
                Add addresses for faster checkout
              </Typography>
            </Box>
          ) : (
            <Grid container spacing={2}>
              {addresses.map((address) => (
                <Grid item xs={12} md={6} key={address.id}>
                  <Card>
                    <CardContent>
                      <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                        <Typography variant="subtitle1" fontWeight="bold">
                          {address.label || 'Address'}
                        </Typography>
                        {address.isDefault && (
                          <Chip label="Default" size="small" color="primary" />
                        )}
                      </Box>
                      <Typography variant="body2" color="text.secondary">
                        {address.address}<br />
                        {address.address2 && <>{address.address2}<br /></>}
                        {address.city}, {address.state} {address.zipCode}
                      </Typography>
                    </CardContent>
                    <CardActions>
                      <Button
                        size="small"
                        onClick={() => setAddressDialog({ open: true, data: address })}
                      >
                        Edit
                      </Button>
                      <Button
                        size="small"
                        color="error"
                        onClick={() => handleAddressDelete(address.id)}
                      >
                        Delete
                      </Button>
                    </CardActions>
                  </Card>
                </Grid>
              ))}
            </Grid>
          )}
        </TabPanel>

        {/* Settings Tab */}
        <TabPanel value={tabValue} index={3}>
          <Stack spacing={3}>
            <Box>
              <Typography variant="h6" gutterBottom>
                Notification Preferences
              </Typography>
              <Stack spacing={2}>
                <FormControlLabel
                  control={
                    <Switch
                      checked={notifications.orderUpdates}
                      onChange={(e) => handleNotificationChange({ orderUpdates: e.target.checked })}
                    />
                  }
                  label="Order Updates"
                />
                <FormControlLabel
                  control={
                    <Switch
                      checked={notifications.promotions}
                      onChange={(e) => handleNotificationChange({ promotions: e.target.checked })}
                    />
                  }
                  label="Promotions & Deals"
                />
                <FormControlLabel
                  control={
                    <Switch
                      checked={notifications.newsletter}
                      onChange={(e) => handleNotificationChange({ newsletter: e.target.checked })}
                    />
                  }
                  label="Newsletter"
                />
              </Stack>
            </Box>
            
            <Divider />
            
            <Box>
              <Typography variant="h6" gutterBottom>
                Security
              </Typography>
              <List>
                <ListItem>
                  <ListItemIcon>
                    <Security />
                  </ListItemIcon>
                  <ListItemText
                    primary="Single Sign-On (SSO)"
                    secondary="You are signed in with your organization's SSO"
                  />
                </ListItem>
                <ListItem>
                  <ListItemIcon>
                    <CreditCard />
                  </ListItemIcon>
                  <ListItemText
                    primary="Payment Methods"
                    secondary="Manage your saved payment methods"
                  />
                  <ListItemSecondaryAction>
                    <Button size="small">Manage</Button>
                  </ListItemSecondaryAction>
                </ListItem>
              </List>
            </Box>
          </Stack>
        </TabPanel>
      </Paper>

      {/* Address Dialog */}
      <Dialog
        open={addressDialog.open}
        onClose={() => setAddressDialog({ open: false, data: null })}
        maxWidth="sm"
        fullWidth
        fullScreen={isMobile}
      >
        <DialogTitle>
          {addressDialog.data?.id ? 'Edit Address' : 'Add New Address'}
        </DialogTitle>
        <DialogContent>
          <Grid container spacing={2} sx={{ mt: 1 }}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Address Label (e.g., Home, Office)"
                value={addressDialog.data?.label || ''}
                onChange={(e) => setAddressDialog({
                  ...addressDialog,
                  data: { ...addressDialog.data, label: e.target.value }
                })}
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Address Line 1"
                value={addressDialog.data?.address || ''}
                onChange={(e) => setAddressDialog({
                  ...addressDialog,
                  data: { ...addressDialog.data, address: e.target.value }
                })}
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Address Line 2 (Optional)"
                value={addressDialog.data?.address2 || ''}
                onChange={(e) => setAddressDialog({
                  ...addressDialog,
                  data: { ...addressDialog.data, address2: e.target.value }
                })}
              />
            </Grid>
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="City"
                value={addressDialog.data?.city || ''}
                onChange={(e) => setAddressDialog({
                  ...addressDialog,
                  data: { ...addressDialog.data, city: e.target.value }
                })}
              />
            </Grid>
            <Grid item xs={12} sm={3}>
              <TextField
                fullWidth
                label="State"
                value={addressDialog.data?.state || ''}
                onChange={(e) => setAddressDialog({
                  ...addressDialog,
                  data: { ...addressDialog.data, state: e.target.value }
                })}
              />
            </Grid>
            <Grid item xs={12} sm={3}>
              <TextField
                fullWidth
                label="ZIP Code"
                value={addressDialog.data?.zipCode || ''}
                onChange={(e) => setAddressDialog({
                  ...addressDialog,
                  data: { ...addressDialog.data, zipCode: e.target.value }
                })}
              />
            </Grid>
            <Grid item xs={12}>
              <FormControlLabel
                control={
                  <Switch
                    checked={addressDialog.data?.isDefault || false}
                    onChange={(e) => setAddressDialog({
                      ...addressDialog,
                      data: { ...addressDialog.data, isDefault: e.target.checked }
                    })}
                  />
                }
                label="Set as default address"
              />
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setAddressDialog({ open: false, data: null })}>
            Cancel
          </Button>
          <Button variant="contained" onClick={handleAddressSubmit}>
            Save
          </Button>
        </DialogActions>
      </Dialog>
    </Container>
  );
}

export default ProfilePage;