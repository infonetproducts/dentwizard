#!/bin/bash
# API Endpoint Testing Script
# Tests all endpoints to see which are working

API_BASE="https://dentwizard.lgstore.com/lg/API/v1"

echo "================================"
echo "Testing DentWizard API Endpoints"
echo "================================"
echo ""

# Color codes for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to test endpoint
test_endpoint() {
    local endpoint=$1
    local description=$2
    
    echo -n "Testing $description: "
    
    # Make request and capture response code
    response=$(curl -s -o /dev/null -w "%{http_code}" "$API_BASE/$endpoint")
    
    if [ "$response" = "200" ]; then
        echo -e "${GREEN}✓ Working${NC} (200)"
        return 0
    elif [ "$response" = "500" ]; then
        echo -e "${RED}✗ Server Error${NC} (500) - Likely PHP syntax issue"
        return 1
    elif [ "$response" = "404" ]; then
        echo -e "${YELLOW}✗ Not Found${NC} (404)"
        return 1
    else
        echo -e "${YELLOW}? Response code: $response${NC}"
        return 1
    fi
}

echo "=== Basic Connectivity ===="
test_endpoint "test.php" "Test endpoint"
echo ""

echo "=== Product Endpoints ===="
test_endpoint "products/list.php?client_id=244&limit=2" "Product list"
test_endpoint "products/detail.php?id=1" "Product detail"
test_endpoint "products/sale-price.php" "Sale price check"
echo ""

echo "=== Category Endpoints ===="
test_endpoint "categories/list.php" "Category list"
echo ""

echo "=== Cart Endpoints ===="
test_endpoint "cart/get.php" "Get cart"
test_endpoint "cart/add.php" "Add to cart"
echo ""

echo "=== Budget Endpoints ===="
test_endpoint "budget/check.php?user_id=1" "Budget check"
test_endpoint "budget/status.php" "Budget status"
echo ""

echo "=== Search Endpoints ===="
test_endpoint "search/products.php?q=shirt" "Product search"
echo ""

echo "=== Gift Card Endpoints ===="
test_endpoint "giftcard/validate.php" "Gift card validation"
echo ""

echo "=== Coupon Endpoints ===="
test_endpoint "coupon/validate.php" "Coupon validation"
echo ""

echo "=== Auth Endpoints ===="
test_endpoint "auth/validate.php" "Auth validation"
echo ""

echo "=== User Endpoints ===="
test_endpoint "user/profile.php" "User profile"
test_endpoint "user/budget.php" "User budget"
echo ""

echo "================================"
echo "Testing Complete"
echo "================================"
echo ""
echo "If endpoints show 500 errors, check:"
echo "1. PHP syntax errors (PHP 5.6 compatibility)"
echo "2. Missing required files"
echo "3. Database connection issues"
echo ""
echo "Check error log at:"
echo "/home/rwaf/public_html/lg/API/logs/error.log"