// Test Script for DentWizard PHP Files
// Run this in your browser console (F12) while on http://localhost:3000

async function testPHPFiles() {
    console.log('Testing DentWizard PHP Files...\n');
    
    // Test 1: Basic PHP Test
    console.log('1. Testing basic PHP connection...');
    try {
        const testResponse = await fetch('http://localhost:3000/lg/API/v1/orders/test-correct.php');
        const testText = await testResponse.text();
        console.log('Basic Test Response:', testText);
    } catch (error) {
        console.error('Basic test failed:', error);
    }
    
    // Test 2: Check Table Structure
    console.log('\n2. Checking table structure...');
    try {
        const structureResponse = await fetch('http://localhost:3000/lg/API/v1/orders/check-structure.php');
        const structureText = await structureResponse.text();
        console.log('Table Structure:', structureText);
    } catch (error) {
        console.error('Structure check failed:', error);
    }
    
    // Test 3: Check Recent Order Attributes
    console.log('\n3. Checking recent order attributes...');
    try {
        const attributesResponse = await fetch('http://localhost:3000/lg/API/v1/orders/check-attributes.php');
        const attributesText = await attributesResponse.text();
        console.log('Recent Order Attributes:', attributesText);
    } catch (error) {
        console.error('Attributes check failed:', error);
    }
    
    return 'Testing complete - check console output above';
}

// Run the tests
testPHPFiles();
