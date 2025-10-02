<?php
// config/jwt.php
// SUPER SIMPLE VERSION - No Firebase JWT Required
// This version works immediately without any libraries

/**
 * Simple JWT Configuration for PHP 5.6
 * Works without Firebase JWT library
 */
class JWTConfig {
    private static $secret_key = 'your-secret-key-change-this-in-production';
    private static $expiry = 3600;
    
    /**
     * Generate a simple token (base64 encoded JSON)
     */
    public static function generateToken($user_data) {
        $payload = array(
            'user_id' => isset($user_data['id']) ? $user_data['id'] : null,
            'email' => isset($user_data['email']) ? $user_data['email'] : null,
            'client_id' => isset($user_data['client_id']) ? $user_data['client_id'] : null,
            'exp' => time() + self::$expiry,
            'iat' => time()
        );
        
        // Simple encoding - not cryptographically secure but works for testing
        $header = base64_encode(json_encode(array('typ' => 'JWT', 'alg' => 'none')));
        $payload = base64_encode(json_encode($payload));
        
        return $header . '.' . $payload . '.no-signature';
    }
    
    /**
     * Validate a simple token
     */
    public static function validateToken($token) {
        if (!$token) {
            return false;
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        $payload = @json_decode(base64_decode($parts[1]), true);
        
        if (!$payload) {
            return false;
        }
        
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
        return self::$expiry;
    }
}

// Helper functions for compatibility
if (!function_exists('generateJWT')) {
    function generateJWT($user_data) {
        return JWTConfig::generateToken($user_data);
    }
}

if (!function_exists('validateJWT')) {
    function validateJWT($token) {
        return JWTConfig::validateToken($token);
    }
}