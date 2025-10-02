// Check if latest order has attributes saved
async function checkLatestOrder() {
    const response = await fetch('http://localhost:3000/lg/API/v1/orders/my-orders.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            userEmail: localStorage.getItem('userEmail'),
            token: localStorage.getItem('token')
        })
    });
    
    const data = await response.json();
    if (data.success && data.orders && data.orders.length > 0) {
        const latestOrder = data.orders[0];
        console.log('Latest Order:', latestOrder.order_id);
        console.log('Order Items:');
        latestOrder.items.forEach(item => {
            console.log(`- ${item.name}`);
            console.log(`  Size: ${item.size || 'NOT SAVED'}`);
            console.log(`  Color: ${item.color || 'NOT SAVED'}`);
            console.log(`  Artwork: ${item.artwork || 'NOT SAVED'}`);
        });
    }
}

checkLatestOrder();
