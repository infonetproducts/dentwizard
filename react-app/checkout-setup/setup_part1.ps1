$checkoutContent = @'
import React, { useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  Container,
  Grid,
  Paper,
  Typography,
  TextField,
  Button,
  Stepper,
  Step,
  StepLabel,
  FormControlLabel,
  Checkbox,
  Radio,
  RadioGroup,
  Divider,
  List,
  ListItem,
  ListItemText,
  Alert,
  CircularProgress,
  useTheme,
  useMediaQuery,
  Card,
  CardContent,
  IconButton,
  Collapse,
  FormControl,
  FormLabel,
  InputAdornment,
  Select,
  MenuItem
} from '@mui/material';
import {
  ShoppingCart,
  LocalShipping,
  Payment,
  CheckCircle,
  ArrowBack,
  Edit,
  ExpandMore,
  ExpandLess,
  Lock,
  CreditCard,
  LocationOn,
  Person
} from '@mui/icons-material';
import { clearCart } from '../store/slices/cartSlice';
import api from '../services/api';
import taxService from '../services/taxService';
import shippingService from '../services/shippingService';

const steps = ['Shipping Info', 'Payment Method', 'Review Order'];
'@

# Write the first part
Set-Content -Path "C:\Users\jkrug\OneDrive\AI\Claude\dentwizard\react-app\src\pages\CheckoutPage.js" -Value $checkoutContent

Write-Host "CheckoutPage.js part 1 created successfully!"