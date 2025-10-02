// Test the Addresses API Endpoint
// Run these commands in your browser console or use the URLs directly

// Test 1: Basic GET request to check if API responds
console.log('Testing Addresses API...');

// Replace with your actual domain
const API_BASE = 'http://localhost/lg/API/v1';  // Change to your domain

// Test GET request
fetch(`${API_BASE}/user/addresses?user_id=19346`)
  .then(response => response.json())
  .then(data => {
    console.log('✅ API Response:', data);
    if (data.status === 'success') {
      console.log(`Found ${data.data.length} saved addresses`);
      if (data.data.length === 0) {
        console.log('No addresses yet - this is normal for first run');
      }
    }
  })
  .catch(error => {
    console.error('❌ Error:', error);
    console.log('Check that the file is at: /lg/API/v1/user/addresses.php');
  });

// Test 2: Try saving a test address
const testAddress = {
  nickname: 'Test Office',
  first_name: 'Joe',
  last_name: 'Lorenzo',
  address1: '123 Test Street',
  address2: '',
  city: 'Erie',
  state: 'PA',
  zip: '16501',
  country: 'United States',
  phone: '555-1234',
  is_default: true
};

console.log('Testing POST to save address...');
fetch(`${API_BASE}/user/addresses?user_id=19346`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify(testAddress)
})
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      console.log('✅ Address saved successfully!', data);
      console.log('Now fetch addresses again to see it...');
      
      // Fetch again to see the saved address
      return fetch(`${API_BASE}/user/addresses?user_id=19346`);
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.data && data.data.length > 0) {
      console.log('✅ Addresses now in database:', data.data);
    }
  })
  .catch(error => {
    console.error('❌ POST Error:', error);
  });
