<?php
// config/jwt.php
// PHP 5.6 Compatible JWT Configuration
// NO use statements to avoid errors if JWT library not installed

// Only load Firebase JWT if using JWT auth (not required for basic operation)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * JWT Configuration for API Authentication
 * PHP 5.6 Compatible - No PHP 7+ syntax, No use statements
 */
class JWTConfig {
    private static $secret_key = null;
    private static $expiry = 3600; // 1 hour default
    private static $initialized = false;
    
    /**
     * Initialize JWT configuration
     */
    private static function init() {
        if (self::$initialized) {
            return;
        }
        
        // Load from environment or use defaults
        // PHP 5.6 compatible - using ternary instead of null coalescing
        $jwt_secret = getenv('JWT_SECRET_KEY');
        self::$secret_key = $jwt_secret ? $jwt_secret : 'your-secret-key-change-this-in-production';
        
        $jwt_expiry = getenv('JWT_EXPIRY');
        self::$expiry = $jwt_expiry ? (int)$jwt_expiry : 3600;
        
        self::$initialized = true;
    }
    
    /**
     * Generate a JWT token
     */
    public static function generateToken($user_data) {
        self::init();
        
        // Check if JWT class is available (using fully qualified name)
        if (!class_exists('\Firebase\JWT\JWT')) {
            // Return a simple base64 token if JWT library not available
            $simple_token = base64_encode(json_encode(array(
                'user_id' => isset($user_data['id']) ? $user_data['id'] : null,
                'email' => isset($user_data['email']) ? $user_data['email'] : null,
                'client_id' => isset($user_data['client_id']) ? $user_data['client_id'] : null,
                'exp' => time() + self::$expiry
            )));
            return $simple_token;
        }
        
        $payload = array(
            'iss' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
            'aud' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
            'iat' => time(),
            'exp' => time() + self::$expiry,
            'user_id' => isset($user_data['id']) ? $user_data['id'] : null,
            'email' => isset($user_data['email']) ? $user_data['email'] : null,
            'client_id' => isset($user_data['client_id']) ? $user_data['client_id'] : null,
            'roles' => isset($user_data['roles']) ? $user_data['roles'] : array('user')
        );
        
        // Use fully qualified class name instead of 'use' statement
        return \Firebase\JWT\JWT::encode($payload, self::$secret_key, 'HS256');
    }
    
    /**
     * Validate a JWT token
     */
    public static function validateToken($token) {
        self::init();
        
        // Check if JWT class is available (using fully qualified name)
        if (!class_exists('\Firebase\JWT\JWT')) {
            // Simple base64 decode if JWT library not available
            $decoded = @json_decode(base64_decode($token), true);
            if ($decoded && isset($decoded['exp']) && $decoded['exp'] > time()) {
                return $decoded;
            }
            return false;
        }
        
        try {
            // Check which version of Firebase JWT is installed
            if (class_exists('\Firebase\JWT\Key')) {
                // Firebase JWT v6+ (requires PHP 7.1+, shouldn't happen on PHP 5.6)
                $key = new \Firebase\JWT\Key(self::$secret_key, 'HS256');
                $decoded = \Firebase\JWT\JWT::decode($token, $key);
            } else {
                // Firebase JWT v5 (PHP 5.6 compatible)
                $decoded = \Firebase\JWT\JWT::decode($token, self::$secret_key, array('HS256'));
            }
            
            // Convert to array for consistency
            return (array)$decoded;
        } catch (Exception $e) {
            // Log error but don't expose details
            error_log('JWT validation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get secret key (for testing only)
     */
    public static function getSecretKey() {
        self::init();
        return self::$secret_key;
    }
    
    /**
     * Get token expiry time
     */
    public static function getExpiry() {
        self::init();
        return self::$expiry;
    }
}

// Optional: Helper functions for backward compatibility
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