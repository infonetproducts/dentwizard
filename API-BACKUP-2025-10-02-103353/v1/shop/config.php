<?php
// api/v1/shop/config.php
// Get shop configuration

require_once '../../config/cors.php';
require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Validate authentication
AuthMiddleware::validateRequest();
$client_id = $GLOBALS['auth_user']['client_id'];

$pdo = getPDOConnection();
$base_url = getenv('BASE_URL') ?: 'https://your-php-server.com';

// Get client configuration
$stmt = $pdo->prepare("SELECT * FROM Clients WHERE ID = :client_id");
$stmt->execute(['client_id' => $client_id]);
$client = $stmt->fetch();

if (!$client) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Shop configuration not found'
    ]);
    exit;
}

// Build logo URL
$logo_url = null;
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/lg_files/gfx/' . $client_id . '/portallogo.png')) {
    $logo_url = $base_url . '/gfx/' . $client_id . '/portallogo.png';
}

// Check sale status
$is_sale_active = false;
if ($client['is_enable_sale']) {
    $today = date('Y-m-d');
    if (($client['sale_start_date'] == '0000-00-00' || $client['sale_start_date'] <= $today) &&
        ($client['sale_end_date'] == '0000-00-00' || $client['sale_end_date'] >= $today)) {
        $is_sale_active = true;
    }
}

// Return configuration
echo json_encode([
    'success' => true,
    'data' => [
        'client_id' => (int)$client_id,
        'shop_name' => $client['Name'],
        'logo_url' => $logo_url,
        'is_enable_sale' => (bool)$client['is_enable_sale'],
        'percentage_off' => (float)$client['percentage_off'],
        'sale_start_date' => $client['sale_start_date'],
        'sale_end_date' => $client['sale_end_date'],
        'is_sale_active' => $is_sale_active,
        'shop_template' => $client['shop_template'],
        'theme' => $client['fullfilment_site_theme'],
        'tax_settings' => [
            'apply_pa_tax' => true, // You may want to make this configurable
            'tax_rate' => 6.0 // Default PA tax rate
        ],
        'features' => [
            'custom_orders' => true,
            'gift_cards' => true,
            'dealer_mode' => $client['shop_template'] == 'Dealer_Tire_Custom_Shop'
        ]
    ]
]);
?>