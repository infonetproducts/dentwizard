// Quick test to see what data is being sent to create.php
async function testOrderData() {
    // Simulate order data like CheckoutPage sends
    const testOrder = {
        userEmail: localStorage.getItem('userEmail'),
        token: localStorage.getItem('token'),
        orderTotal: 85.00,
        shipping: 10.00,
        shippingInfo: {
            name: "Test User",
            address: "123 Test St",
            city: "Miami",
            state: "FL",
            postal: "33101",
            phone: "555-1234"
        },
        items: [
            {
                product_id: 1,
                product_name: "Test Product",
                quantity: 1,
                price: 75.00,
                size: "XL",
                color: "Navy",
                artwork: "Dent Wizard Logo"
            }
        ]
    };
    
    const response = await fetch('http://localhost:3000/lg/API/v1/orders/create-debug-v2.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(testOrder)
    });
    
    const data = await response.json();
    console.log('Debug Response:', data);
    console.log('\nCheck debug_info to see:');
    console.log('1. If items array is received');
    console.log('2. If size, color, artwork are in the items');
}

testOrderData();
