<?php
// TEMPORARY FIX: Simple JWT stub for PHP 5.6
// Upload this as jwt.php to quickly fix the error

/**
 * Temporary JWT Configuration for PHP 5.6
 * This is a simplified version that works without Firebase JWT library
 * Replace with full version once Firebase JWT is installed
 */

class JWTConfig {
    private static $secret_key = 'temporary-secret-key';
    
    /**
     * Generate a simple token (not cryptographically secure - temporary only)
     */
    public static function generateToken($user_data) {
        $header = base64_encode(json_encode(array('typ' => 'JWT', 'alg' => 'none')));
        $payload = base64_encode(json_encode(array(
            'user_id' => isset($user_data['id']) ? $user_data['id'] : null,
            'email' => isset($user_data['email']) ? $user_data['email'] : null,
            'client_id' => isset($user_data['client_id']) ? $user_data['client_id'] : null,
            'exp' => time() + 3600
        )));
        
        return $header . '.' . $payload . '.temp';
    }
    
    /**
     * Validate a simple token (not secure - temporary only)
     */
    public static function validateToken($token) {
        if (!$token) {
            return false;
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        $payload = json_decode(base64_decode($parts[1]), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    public static function getSecretKey() {
        return self::$secret_key;
    }
    
    public static function getExpiry() {
        return 3600;
    }
}

// Helper functions for compatibility
function generateJWT($user_data) {
    return JWTConfig::generateToken($user_data);
}

function validateJWT($token) {
    return JWTConfig::validateToken($token);
}

// This allows the API to work without Firebase JWT library
// Note: This is NOT secure for production - only for testing