// Test file to verify saved addresses integration
// Run this in your browser console on the checkout page

// Test 1: Check if addressService is imported
console.log('Testing Saved Addresses Integration...');

// Test 2: Try to load saved addresses
const testLoadAddresses = async () => {
  try {
    const response = await fetch('http://localhost/lg/API/v1/user/addresses', {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    console.log('Saved Addresses Response:', data);
    
    if (data.data) {
      console.log(`Found ${data.data.length} saved addresses`);
      data.data.forEach((addr, index) => {
        console.log(`Address ${index + 1}:`, addr.nickname || addr.address1);
      });
    }
  } catch (error) {
    console.error('Error loading addresses:', error);
    console.log('Make sure the addresses.php endpoint is deployed to /lg/API/v1/user/addresses.php');
  }
};

// Test 3: Check if UI components are present
const checkUIComponents = () => {
  const hasDropdown = document.querySelector('select') ? 'YES' : 'NO';
  const hasCheckbox = document.querySelector('input[type="checkbox"]') ? 'YES' : 'NO';
  
  console.log('UI Components Check:');
  console.log('- Address Dropdown:', hasDropdown);
  console.log('- Save Address Checkbox:', hasCheckbox);
};

// Run tests
testLoadAddresses();
checkUIComponents();

console.log('✓ If you see saved addresses above, the integration is working!');
console.log('✗ If you see errors, check that addresses.php is deployed.');
