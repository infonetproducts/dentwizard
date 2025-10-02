<?php
/**
 * API Security Configuration
 * Allows different security levels for development/testing/production
 */

// config/security.php

// Environment detection
$environment = getenv('ENVIRONMENT') ?: 'development';
$api_key = getenv('API_KEY') ?: 'test_key_change_in_production';
$bypass_auth = getenv('BYPASS_AUTH') === 'true';

/**
 * Security Levels:
 * 1. Development (localhost) - Relaxed security
 * 2. Staging (Render) - API key required
 * 3. Production - Full authentication required
 */

function validateRequest() {
    global $environment, $api_key, $bypass_auth;
    
    // Get request details
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    $request_api_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
    // 1. BYPASS MODE (Testing only - NEVER in production)
    if ($bypass_auth && $environment === 'development') {
        // Add warning header so you know bypass is active
        header('X-Security-Mode: BYPASSED-DEVELOPMENT-ONLY');
        return true;
    }
    
    // 2. ALLOWED ORIGINS CHECK
    $allowed_origins = [
        'http://localhost:3000',                    // Local React dev
        'http://localhost:3001',                    // Alternative local port
        'https://your-app.onrender.com',           // Render deployment
        'https://dentwizard-apparel.onrender.com', // Production Render
        'https://apparel.dentwizard.com'           // Custom domain
    ];
    
    // Allow if from trusted origin
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
        header('X-Security-Mode: ORIGIN-VERIFIED');
        
        // For development, origin might be enough
        if ($environment === 'development') {
            return true;
        }
    }
    
    // 3. API KEY VALIDATION (For Render/Staging)
    if (!empty($request_api_key)) {
        if ($request_api_key === $api_key) {
            header('X-Security-Mode: API-KEY-VALID');
            return true;
        } else {
            http_response_code(401);
            die(json_encode([
                'error' => 'Invalid API key',
                'security_mode' => 'API-KEY-REQUIRED'
            ]));
        }
    }
    
    // 4. JWT TOKEN VALIDATION (For Production)
    if (!empty($auth_header) && strpos($auth_header, 'Bearer ') === 0) {
        $token = substr($auth_header, 7);
        
        // Validate JWT token (use your existing auth middleware)
        require_once __DIR__ . '/../middleware/auth.php';
        $decoded = AuthMiddleware::validateToken($token);
        
        if ($decoded) {
            header('X-Security-Mode: JWT-AUTHENTICATED');
            return $decoded; // Return user data
        }
    }
    
    // 5. SSO TOKEN VALIDATION (When Azure AD is ready)
    if (!empty($_SESSION['sso_token'])) {
        header('X-Security-Mode: SSO-AUTHENTICATED');
        return true;
    }
    
    // 6. LOCALHOST EXCEPTION (Development only)
    if ($environment === 'development') {
        $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
        $is_localhost = in_array($remote_addr, ['127.0.0.1', '::1', 'localhost']);
        
        if ($is_localhost) {
            header('X-Security-Mode: LOCALHOST-ALLOWED');
            return true;
        }
    }
    
    // 7. FAIL - No valid authentication
    http_response_code(401);
    die(json_encode([
        'error' => 'Authentication required',
        'security_mode' => 'NONE',
        'origin' => $origin,
        'environment' => $environment,
        'hint' => 'Provide API key or authentication token'
    ]));
}

/**
 * Rate limiting function to prevent abuse
 */
function checkRateLimit($identifier = null) {
    global $environment;
    
    // Skip rate limiting in development
    if ($environment === 'development') {
        return true;
    }
    
    // Use IP if no identifier provided
    if (!$identifier) {
        $identifier = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    }
    
    // Implement your rate limiting logic here
    require_once __DIR__ . '/../helpers/rate_limit.php';
    checkRateLimit($identifier, 1000, 3600); // 1000 requests per hour
}

/**
 * Log security events for monitoring
 */
function logSecurityEvent($event_type, $details = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event_type,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'none',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    
    $log_file = __DIR__ . '/../logs/security.log';
    error_log(json_encode($log_entry) . "\n", 3, $log_file);
}

/**
 * Check if request is from a bot/crawler
 */
function isBot() {
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $bot_patterns = ['bot', 'crawler', 'spider', 'scraper', 'curl', 'postman'];
    
    foreach ($bot_patterns as $pattern) {
        if (strpos($user_agent, $pattern) !== false) {
            // Allow Postman in development
            if ($pattern === 'postman' && $GLOBALS['environment'] === 'development') {
                return false;
            }
            return true;
        }
    }
    
    return false;
}

// Auto-initialize security for all requests
function initializeSecurity() {
    global $environment;
    
    // Set security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // HTTPS enforcement (skip in development)
    if ($environment !== 'development') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // Block bots except in development
    if (isBot() && $environment !== 'development') {
        http_response_code(403);
        die(json_encode(['error' => 'Automated access not allowed']));
    }
    
    // CORS preflight handling
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        header('Access-Control-Max-Age: 86400'); // 24 hours
        exit(0);
    }
}

// Initialize on include
initializeSecurity();