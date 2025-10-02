// Test to check if the last order has attributes saved
async function checkLastOrder() {
    try {
        console.log('🔍 Checking last order...');
        
        // Get the current user's email (Jamie's logged in)
        const user = JSON.parse(localStorage.getItem('user'));
        console.log('✅ User email:', user?.email);
        
        // Fetch orders using the my-orders.php API
        const response = await fetch('/lg/API/v1/orders/my-orders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: user?.email
            })
        });
        
        const data = await response.json();
        console.log('📦 API Response:', data);
        
        if (data.success && data.orders && data.orders.length > 0) {
            // Get the most recent order (first in the list)
            const lastOrder = data.orders[0];
            console.log('\n🎯 MOST RECENT ORDER:');
            console.log('Order Number:', lastOrder.order_number);
            console.log('Order Date:', lastOrder.order_date);
            console.log('Total:', lastOrder.total_amount);
            console.log('Status:', lastOrder.status);
            
            console.log('\n📋 ORDER ITEMS:');
            lastOrder.items.forEach((item, index) => {
                console.log(`\nItem ${index + 1}: ${item.product_name}`);
                console.log('  Quantity:', item.quantity);
                console.log('  Price:', item.price);
                console.log('  ✅ Size:', item.size || '❌ NOT SAVED');
                console.log('  ✅ Color:', item.color || '❌ NOT SAVED');
                console.log('  ✅ Artwork:', item.artwork || '❌ NOT SAVED');
            });
            
            // Check if attributes are missing
            const itemsWithoutAttributes = lastOrder.items.filter(item => 
                !item.size && !item.color && !item.artwork
            );
            
            if (itemsWithoutAttributes.length > 0) {
                console.warn(`\n⚠️ ${itemsWithoutAttributes.length} items missing attributes!`);
                console.warn('The attributes are NOT being saved to the database.');
            } else {
                console.log('\n✅ SUCCESS! All items have attributes saved!');
            }
            
            return lastOrder;
        } else {
            console.error('❌ No orders found or API error:', data);
        }
    } catch (error) {
        console.error('❌ Error checking order:', error);
    }
}

// Run the test
checkLastOrder();