#!/bin/bash
# Test API endpoints from command line

echo "Testing Tax API..."
curl -X POST https://dentwizard.lgstore.com/lg/API/v1/tax/calculate.php \
  -H "Content-Type: application/json" \
  -d '{
    "to_state": "CA",
    "to_zip": "90210",
    "to_city": "Beverly Hills",
    "amount": 100,
    "shipping": 10,
    "line_items": [{
      "id": "1",
      "quantity": 1,
      "unit_price": 100,
      "product_tax_code": "20010"
    }]
  }'

echo -e "\n\nTesting Shipping API..."
curl https://dentwizard.lgstore.com/lg/API/v1/shipping/methods.php?client_id=1&subtotal=65

echo -e "\n\nTesting Budget API..."
curl https://dentwizard.lgstore.com/lg/API/v1/user/budget.php