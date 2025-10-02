// Debug helper to check profile budget data
import React, { useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { fetchUserProfile } from '../store/slices/profileSlice';

const ProfileDebug = () => {
  const dispatch = useDispatch();
  const profile = useSelector((state) => state.profile);
  const auth = useSelector((state) => state.auth);
  
  useEffect(() => {
    dispatch(fetchUserProfile());
  }, [dispatch]);
  
  useEffect(() => {
    console.log('=== PROFILE DATA ===');
    console.log('Profile User:', profile.user);
    console.log('Profile Budget:', profile.budget);
    console.log('Auth User:', auth.user);
    console.log('===================');
  }, [profile, auth]);
  
  return (
    <div style={{ padding: 20, background: '#f0f0f0', margin: 20 }}>
      <h3>Profile Debug</h3>
      <pre>{JSON.stringify({ profile, auth }, null, 2)}</pre>
    </div>
  );
};

export default ProfileDebug;