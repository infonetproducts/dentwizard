<?php
/**
 * CORS Headers Configuration
 * PHP 5.6 Compatible Version
 * Add this to the top of EVERY API endpoint file
 */

// Allow from specific origins (more secure)
$allowed_origins = array(
    'http://localhost:3000',
    'http://localhost:3001', 
    'http://localhost:3002',
    'http://localhost:3003',
    'http://localhost:3004',
    'http://localhost:3005',
    'https://dentwizard.lgstore.com',
    'https://dentwizard-app-staging.onrender.com',  // Staging Render deployment
    'https://dentwizard.onrender.com',  // Alternative staging
    'https://dentwizard-prod.onrender.com',  // Production Render deployment
    'https://dentwizardapparel.com',  // Production custom domain
    'https://www.dentwizardapparel.com'  // Production custom domain (www)
);

// Try to determine origin from HTTP_ORIGIN or HTTP_REFERER
$origin = '';
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
}

// If no origin header, try to extract from referer
if (empty($origin) && isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $parsed = parse_url($referer);
    if ($parsed) {
        $origin = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port']) && !in_array($parsed['port'], array(80, 443))) {
            $origin .= ':' . $parsed['port'];
        }
    }
}

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Default to localhost for development
    header("Access-Control-Allow-Origin: http://localhost:3000");
}

// Allow specific methods
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID, X-Auth-Token");

// Allow credentials
header("Access-Control-Allow-Credentials: true");

// Set max age for preflight requests (cache for 1 hour)
header("Access-Control-Max-Age: 3600");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return immediately for preflight requests
    http_response_code(200);
    exit();
}
?>
