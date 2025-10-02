# Tax Calculation Setup

## Overview
The tax calculation system integrates with TaxJar API for accurate sales tax calculation across all US states.

## Configuration

### 1. TaxJar API Key
You need to add your TaxJar API key to the PHP configuration. 

In your PHP backend, update the tax_calculate.php file:
```php
$taxjar_api_key = 'YOUR_ACTUAL_TAXJAR_API_KEY';
```

Or better, store it in your config file:
```php
// In your config/settings.php or include/config.php
define('TAXJAR_API_KEY', 'your_actual_api_key_here');
```

### 2. How It Works

The tax calculation system has three layers:

1. **TaxJar API Integration** (Primary)
   - Provides accurate, real-time tax rates
   - Handles complex tax jurisdictions
   - Accounts for product-specific tax codes
   - Manages nexus requirements

2. **Fallback Calculation** (Backup)
   - Uses state-level tax rates for all 50 states + DC
   - Activates if TaxJar API is unavailable
   - Provides approximate tax calculation

3. **Zero Tax** (Final fallback)
   - Returns 0% tax if all methods fail
   - Prevents checkout from breaking

## Tax Rates by State (Fallback)

The system includes fallback rates for all US states:

### No Sales Tax States:
- Alaska (AK) - 0%
- Delaware (DE) - 0%
- Montana (MT) - 0%
- New Hampshire (NH) - 0%
- Oregon (OR) - 0%

### Other States:
- California (CA) - 7.25%
- Texas (TX) - 6.25%
- Florida (FL) - 6%
- New York (NY) - 4% (base)
- Pennsylvania (PA) - 6%
- And all other states...

## API Endpoint

**URL**: `/lg/API/v1/tax/calculate.php`

**Method**: POST

**Request Body**:
```json
{
  "to_state": "CA",
  "to_zip": "90210",
  "to_city": "Beverly Hills",
  "to_street": "123 Main St",
  "amount": 65.00,
  "shipping": 10.00,
  "line_items": [
    {
      "id": "1",
      "quantity": 1,
      "unit_price": 65.00,
      "product_tax_code": ""
    }
  ]
}
```

**Response**:
```json
{
  "tax": 5.44,
  "rate": 0.0725,
  "taxable_amount": 75.00,
  "breakdown": {...},
  "tax_source": "taxjar"
}
```

## Frontend Integration

The checkout page automatically calculates tax when:
1. User enters complete shipping address
2. Address includes: street, city, state, and ZIP
3. Cart has items with prices

## Troubleshooting

### Tax showing $0.00:
1. Check browser console for API errors
2. Verify TaxJar API key is configured
3. Ensure address fields are complete
4. Check network tab for API response

### Getting fallback rates:
- Response includes `"tax_source": "fallback"`
- Check `error_message` field for details

### Testing:
- California address should show ~7.25% tax
- Oregon address should show 0% tax
- Pennsylvania address should show 6% tax

## Deployment

1. Upload `tax_calculate.php` to `/lg/API/v1/tax/`
2. Add your TaxJar API key
3. Test with various state addresses
4. Monitor for errors in logs