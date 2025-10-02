import { useEffect } from 'react';
import { useDispatch } from 'react-redux';
import { initializeCart } from '../store/slices/cartSlice';

const AppInitializer = ({ children }) => {
  const dispatch = useDispatch();

  useEffect(() => {
    // Initialize cart from localStorage or server on app load
    dispatch(initializeCart());
    
    // Optional: Set up periodic sync to keep cart alive
    const syncInterval = setInterval(() => {
      // This will refresh the cart from server, preventing session timeout
      dispatch(initializeCart());
    }, 15 * 60 * 1000); // Sync every 15 minutes
    
    return () => clearInterval(syncInterval);
  }, [dispatch]);

  return children;
};

export default AppInitializer;