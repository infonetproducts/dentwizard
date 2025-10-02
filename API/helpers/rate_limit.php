<?php
/**
 * Rate Limiting Helper
 * Prevents API abuse and brute force attacks
 */

function checkRateLimit($identifier, $limit = 100, $window = 3600) {
    // For now, use file-based tracking (upgrade to Redis/Memcached for production)
    $rateLimitDir = __DIR__ . '/../logs/rate_limit/';
    if (!file_exists($rateLimitDir)) {
        mkdir($rateLimitDir, 0777, true);
    }
    
    $file = $rateLimitDir . md5($identifier) . '.json';
    $now = time();
    
    // Read existing data
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        $requests = $data['requests'] ?? [];
        
        // Remove old requests outside the window
        $requests = array_filter($requests, function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });
    } else {
        $requests = [];
    }
    
    // Check if limit exceeded
    if (count($requests) >= $limit) {
        http_response_code(429);
        header('Retry-After: ' . $window);
        die(json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $window
        ]));
    }
    
    // Add current request
    $requests[] = $now;
    
    // Save updated data
    file_put_contents($file, json_encode([
        'identifier' => $identifier,
        'requests' => $requests,
        'last_request' => $now
    ]));
    
    // Set rate limit headers
    header('X-RateLimit-Limit: ' . $limit);
    header('X-RateLimit-Remaining: ' . ($limit - count($requests)));
    header('X-RateLimit-Reset: ' . ($now + $window));
}

/**
 * Special rate limit for sensitive endpoints
 */
function checkSensitiveEndpoint($identifier, $endpoint) {
    $limits = [
        'giftcard_validate' => ['limit' => 10, 'window' => 3600],  // 10 per hour
        'coupon_validate' => ['limit' => 20, 'window' => 3600],    // 20 per hour
        'checkout_process' => ['limit' => 5, 'window' => 600],     // 5 per 10 minutes
        'payment_process' => ['limit' => 3, 'window' => 300],      // 3 per 5 minutes
    ];
    
    $config = $limits[$endpoint] ?? ['limit' => 100, 'window' => 3600];
    checkRateLimit($identifier . ':' . $endpoint, $config['limit'], $config['window']);
}

/**
 * Track failed attempts (for brute force protection)
 */
function trackFailedAttempt($identifier, $resource) {
    $failureDir = __DIR__ . '/../logs/failures/';
    if (!file_exists($failureDir)) {
        mkdir($failureDir, 0777, true);
    }
    
    $file = $failureDir . md5($identifier . $resource) . '.json';
    $now = time();
    
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        $attempts = $data['attempts'] ?? 0;
        $firstAttempt = $data['first_attempt'] ?? $now;
        
        // Reset if it's been more than an hour
        if (($now - $firstAttempt) > 3600) {
            $attempts = 0;
            $firstAttempt = $now;
        }
    } else {
        $attempts = 0;
        $firstAttempt = $now;
    }
    
    $attempts++;
    
    // Block after 5 failed attempts
    if ($attempts > 5) {
        http_response_code(403);
        die(json_encode([
            'success' => false,
            'error' => 'Too many failed attempts. Account temporarily locked.',
            'locked_until' => date('Y-m-d H:i:s', $firstAttempt + 3600)
        ]));
    }
    
    file_put_contents($file, json_encode([
        'identifier' => $identifier,
        'resource' => $resource,
        'attempts' => $attempts,
        'first_attempt' => $firstAttempt,
        'last_attempt' => $now
    ]));
    
    return $attempts;
}

/**
 * Clear failed attempts (call after successful validation)
 */
function clearFailedAttempts($identifier, $resource) {
    $failureDir = __DIR__ . '/../logs/failures/';
    $file = $failureDir . md5($identifier . $resource) . '.json';
    
    if (file_exists($file)) {
        unlink($file);
    }
}
