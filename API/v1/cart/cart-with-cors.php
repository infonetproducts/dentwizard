<?php
// Cart handler with CORS support for Render deployment
session_start();

// CORS configuration for production
$allowed_origins = array(
    'http://localhost:3000',              // Local development
    'http://localhost:3001',              // Alternative local port
    'https://dentwizard.onrender.com',    // Your Render domain (UPDATE THIS)
    'https://dentwizard-store.onrender.com' // Alternative Render domain
);

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-ID");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Rest of your cart.php code continues here...
// (Include all the existing cart functionality)