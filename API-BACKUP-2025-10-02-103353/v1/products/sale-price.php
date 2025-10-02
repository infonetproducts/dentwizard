<?php
// api/v1/products/sale-price.php
// PHP 5.6 Compatible - Get sale price for products based on date

require_once '../../config/cors.php';
require_once '../../config/database.php';

// Get parameters
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 1;
$check_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if (!$product_id) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Product ID is required'
    ));
    exit;
}

$pdo = getPDOConnection();

try {
    // First get the product base price
    $stmt = $pdo->prepare("
        SELECT 
            ID as product_id,
            item_title as name,
            item_price as regular_price,
            sale_price,
            sale_start_date,
            sale_end_date,
            sale_percentage_off
        FROM Items
        WHERE ID = :product_id
        AND CID = :client_id
    ");
    $stmt->execute(array(
        'product_id' => $product_id,
        'client_id' => $client_id
    ));
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(array(
            'success' => false,
            'error' => 'Product not found'
        ));
        exit;
    }
    
    $regular_price = (float)$product['regular_price'];
    $sale_price = (float)$product['sale_price'];
    $sale_percentage = (float)$product['sale_percentage_off'];
    $is_on_sale = false;
    $final_price = $regular_price;
    $savings = 0;
    $savings_percentage = 0;
    
    // Check if product is on sale for the given date
    if ($product['sale_start_date'] && $product['sale_end_date']) {
        $check_timestamp = strtotime($check_date);
        $start_timestamp = strtotime($product['sale_start_date']);
        $end_timestamp = strtotime($product['sale_end_date'] . ' 23:59:59');
        
        if ($check_timestamp >= $start_timestamp && $check_timestamp <= $end_timestamp) {
            $is_on_sale = true;
            
            // Calculate sale price
            if ($sale_percentage > 0) {
                // Use percentage discount
                $final_price = $regular_price * (1 - ($sale_percentage / 100));
            } elseif ($sale_price > 0 && $sale_price < $regular_price) {
                // Use fixed sale price
                $final_price = $sale_price;
            }
            
            $savings = $regular_price - $final_price;
            $savings_percentage = ($savings / $regular_price) * 100;
        }
    }
    
    // Check for global sales
    $stmt = $pdo->prepare("
        SELECT 
            percentage_off,
            start_date,
            end_date
        FROM global_sales
        WHERE client_id = :client_id
        AND status = 'active'
        AND :check_date BETWEEN start_date AND end_date
        ORDER BY percentage_off DESC
        LIMIT 1
    ");
    $stmt->execute(array(
        'client_id' => $client_id,
        'check_date' => $check_date
    ));
    $global_sale = $stmt->fetch();
    
    // Apply global sale if better than product sale
    if ($global_sale && $global_sale['percentage_off']) {
        $global_price = $regular_price * (1 - ($global_sale['percentage_off'] / 100));
        if (!$is_on_sale || $global_price < $final_price) {
            $is_on_sale = true;
            $final_price = $global_price;
            $savings = $regular_price - $final_price;
            $savings_percentage = $global_sale['percentage_off'];
        }
    }
    
    // Check for category-specific sales
    $stmt = $pdo->prepare("
        SELECT 
            cs.percentage_off,
            cs.start_date,
            cs.end_date
        FROM category_sales cs
        JOIN Items i ON i.Category = cs.category_id
        WHERE i.ID = :product_id
        AND cs.client_id = :client_id
        AND cs.status = 'active'
        AND :check_date BETWEEN cs.start_date AND cs.end_date
        ORDER BY cs.percentage_off DESC
        LIMIT 1
    ");
    $stmt->execute(array(
        'product_id' => $product_id,
        'client_id' => $client_id,
        'check_date' => $check_date
    ));
    $category_sale = $stmt->fetch();
    
    // Apply category sale if better
    if ($category_sale && $category_sale['percentage_off']) {
        $category_price = $regular_price * (1 - ($category_sale['percentage_off'] / 100));
        if (!$is_on_sale || $category_price < $final_price) {
            $is_on_sale = true;
            $final_price = $category_price;
            $savings = $regular_price - $final_price;
            $savings_percentage = $category_sale['percentage_off'];
        }
    }
    
    // Return pricing information
    echo json_encode(array(
        'success' => true,
        'data' => array(
            'product_id' => $product_id,
            'product_name' => $product['name'],
            'regular_price' => $regular_price,
            'is_on_sale' => $is_on_sale,
            'sale_price' => $final_price,
            'savings' => $savings,
            'savings_percentage' => round($savings_percentage, 1),
            'sale_dates' => array(
                'start' => $product['sale_start_date'],
                'end' => $product['sale_end_date']
            ),
            'price_to_display' => number_format($final_price, 2),
            'sale_label' => $is_on_sale ? 
                ($savings_percentage > 0 ? 
                    round($savings_percentage) . '% OFF' : 
                    'SALE - Save $' . number_format($savings, 2)
                ) : null
        )
    ));
    
} catch (Exception $e) {
    error_log('Sale price calculation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to calculate sale price'
    ));
}
?>