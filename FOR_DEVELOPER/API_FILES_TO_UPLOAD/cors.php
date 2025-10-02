<?php
/**
 * CORS Headers Configuration for Dentwizard API
 * 
 * Include this file at the top of every API endpoint:
 * <?php require_once 'cors.php'; ?>
 */

// List of allowed origins (UPDATE FOR PRODUCTION!)
$allowed_origins = [
    'http://localhost:3000',
    'http://localhost:3001', 
    'http://localhost:3002',
    'http://localhost:3003',
    'http://localhost:3004',
    'http://localhost:3005',
    'http://localhost:3006',
    'http://localhost:3007',
    'http://localhost:3008',
    'https://dentwizard.lgstore.com',
    // Add your production domain here
];

// Get the origin of the request
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Check if origin is allowed
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // For development, allow all origins
    // CHANGE THIS IN PRODUCTION!
    header("Access-Control-Allow-Origin: *");
}

// Allow specific HTTP methods
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");

// Allow credentials to be included
header("Access-Control-Allow-Credentials: true");

// Cache preflight requests for 1 hour
header("Access-Control-Max-Age: 3600");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // No need to continue processing
    http_response_code(200);
    exit();
}
?>
