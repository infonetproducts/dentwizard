<?php
// middleware/auth.php
// PHP 5.6 Compatible Authentication Middleware

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';

class AuthMiddleware {
    
    /**
     * Validate incoming request
     * PHP 5.6 compatible version
     */
    public static function validateRequest() {
        // Get authorization header
        $headers = self::getAuthHeaders();
        $token = null;
        
        // Check for Bearer token
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                $token = $matches[1];
            }
        }
        
        // Check for token in GET/POST
        if (!$token) {
            $token = isset($_GET['token']) ? $_GET['token'] : null;
            if (!$token) {
                $token = isset($_POST['token']) ? $_POST['token'] : null;
            }
        }
        
        // Also check existing PHP session (for backward compatibility)
        if (!$token && session_id() === '') {
            session_start();
        }
        
        if (!$token && isset($_SESSION['user_id'])) {
            // User is logged in with traditional session
            $GLOBALS['auth_user'] = array(
                'id' => $_SESSION['user_id'],
                'client_id' => isset($_SESSION['client_id']) ? $_SESSION['client_id'] : null,
                'email' => isset($_SESSION['email']) ? $_SESSION['email'] : null,
                'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null,
                'auth_method' => 'session'
            );
            return true;
        }
        
        // If no token and no session, unauthorized
        if (!$token) {
            self::unauthorizedResponse('No authentication token provided');
        }
        
        // Validate JWT token (PHP 5.6 compatible)
        $decoded = self::validateJWT($token);
        
        if (!$decoded) {
            self::unauthorizedResponse('Invalid or expired token');
        }
        
        // Store user data globally
        $GLOBALS['auth_user'] = array(
            'id' => $decoded['user_id'],
            'client_id' => $decoded['client_id'],
            'email' => $decoded['email'],
            'name' => isset($decoded['name']) ? $decoded['name'] : null,
            'auth_method' => 'jwt'
        );
        
        return true;
    }
    
    /**
     * Simple JWT validation for PHP 5.6
     * Note: For production, use firebase/php-jwt library
     */
    private static function validateJWT($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);
        $signature = $parts[2];
        
        // Verify signature
        $secret = getenv('JWT_SECRET') ? getenv('JWT_SECRET') : 'your-secret-key-change-in-production';
        $valid_signature = hash_hmac(
            'sha256',
            $parts[0] . '.' . $parts[1],
            $secret,
            true
        );
        $valid_signature = str_replace(
            array('+', '/', '='),
            array('-', '_', ''),
            base64_encode($valid_signature)
        );
        
        if ($signature !== $valid_signature) {
            return false;
        }
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Get auth headers (PHP 5.6 compatible)
     */
    private static function getAuthHeaders() {
        $headers = array();
        
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        }
        
        // Nginx/PHP-FPM
        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }
        
        return $headers;
    }
    
    /**
     * Send unauthorized response
     */
    private static function unauthorizedResponse($message = 'Unauthorized') {
        http_response_code(401);
        echo json_encode(array(
            'success' => false,
            'error' => $message
        ));
        exit;
    }
    
    /**
     * Optional: Validate client/shop access
     */
    public static function validateClientAccess($required_client_id = null) {
        if (!isset($GLOBALS['auth_user'])) {
            self::unauthorizedResponse('Not authenticated');
        }
        
        $user_client_id = $GLOBALS['auth_user']['client_id'];
        
        if ($required_client_id && $user_client_id != $required_client_id) {
            http_response_code(403);
            echo json_encode(array(
                'success' => false,
                'error' => 'Access denied to this shop'
            ));
            exit;
        }
    }
    
    /**
     * Create JWT token (PHP 5.6 compatible)
     */
    public static function createToken($user_data) {
        $secret = getenv('JWT_SECRET') ? getenv('JWT_SECRET') : 'your-secret-key-change-in-production';
        
        // Header
        $header = json_encode(array('typ' => 'JWT', 'alg' => 'HS256'));
        
        // Payload
        $payload = json_encode(array(
            'user_id' => $user_data['user_id'],
            'client_id' => $user_data['client_id'],
            'email' => $user_data['email'],
            'name' => isset($user_data['name']) ? $user_data['name'] : null,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60) // 7 days
        ));
        
        // Encode
        $base64Header = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($header));
        $base64Payload = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($payload));
        
        // Create signature
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($signature));
        
        // Create JWT
        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }
}
?>