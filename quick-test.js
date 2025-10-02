// Quick API Test - Run in browser console on your checkout page
// Change localhost to your actual domain if needed

fetch('http://localhost/lg/API/v1/user/addresses.php?user_id=19346')
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      console.log('✅ API is working!');
      console.log('Addresses found:', data.data.length);
      console.table(data.data);
    } else {
      console.log('❌ API returned error:', data);
    }
  })
  .catch(err => {
    console.log('❌ Could not reach API:', err);
    console.log('Make sure the file is at: /lg/API/v1/user/addresses.php');
  });