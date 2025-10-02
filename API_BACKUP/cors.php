<?php
/**
 * CORS Headers Configuration
 * Add this to the top of EVERY API endpoint file
 */

// Allow from specific origins (more secure)
$allowed_origins = [
    'http://localhost:3000',
    'http://localhost:3001', 
    'http://localhost:3002',
    'http://localhost:3003',
    'http://localhost:3004',
    'http://localhost:3005',
    'https://dentwizard.lgstore.com',
    'https://dentwizard-app.onrender.com',  // Production Render deployment
    'https://dentwizard-app-staging.onrender.com'  // Staging Render deployment
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Default to first allowed origin if origin is not recognized
    header("Access-Control-Allow-Origin: " . $allowed_origins[0]);
}

// Allow specific methods
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");

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