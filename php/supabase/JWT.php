<?php
/**
 * JWT Helper Class for Ebakunado
 * Handles JWT token generation, verification, and refresh
 */

// Include Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JWT {
    // JWT Secret Key - CHANGE THIS IN PRODUCTION!
    // Store in environment variable or config file for security
    private static $secret_key = 'ebakunado_jwt_secret_key_change_in_production_2025';
    
    // Token expiration time (default: 24 hours)
    private static $expiration = 86400; // 24 hours in seconds
    
    // Token algorithm
    private static $algorithm = 'HS256';
    
    /**
     * Get JWT secret key from config or use default
     */
    private static function getSecretKey() {
        // Try to get from config file (you can add this to SupabaseConfig.php)
        global $jwt_secret_key;
        
        if (isset($jwt_secret_key) && !empty($jwt_secret_key)) {
            return $jwt_secret_key;
        }
        
        // Try environment variable
        $env_key = getenv('JWT_SECRET_KEY');
        if ($env_key !== false) {
            return $env_key;
        }
        
        // Use default (change this in production!)
        return self::$secret_key;
    }
    
    /**
     * Generate JWT token for user
     * 
     * @param array $user_data User data (user_id, email, fname, lname, etc.)
     * @param string $user_type User type (super_admin, admin, bhw, midwife, user)
     * @param int $expiration Optional custom expiration time in seconds
     * @return string JWT token
     */
    public static function generateToken($user_data, $user_type, $expiration = null) {
        $secret = self::getSecretKey();
        $exp = $expiration ?: self::$expiration;
        
        // Get user ID based on user type
        $user_id = null;
        switch ($user_type) {
            case 'super_admin':
                $user_id = $user_data['super_admin_id'] ?? null;
                break;
            case 'admin':
                $user_id = $user_data['admin_id'] ?? null;
                break;
            case 'bhw':
                $user_id = $user_data['bhw_id'] ?? null;
                break;
            case 'midwife':
                $user_id = $user_data['midwife_id'] ?? null;
                break;
            case 'user':
                $user_id = $user_data['user_id'] ?? null;
                break;
        }
        
        // Payload data
        $payload = [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'email' => $user_data['email'] ?? null,
            'fname' => $user_data['fname'] ?? null,
            'lname' => $user_data['lname'] ?? null,
            'iat' => time(), // Issued at
            'exp' => time() + $exp // Expiration
        ];
        
        // Add optional fields if available
        if (isset($user_data['phone_number'])) {
            $payload['phone_number'] = $user_data['phone_number'];
        }
        
        if (isset($user_data['profileimg'])) {
            $payload['profileimg'] = $user_data['profileimg'];
        }
        
        try {
            $token = FirebaseJWT::encode($payload, $secret, self::$algorithm);
            return $token;
        } catch (Exception $e) {
            error_log("JWT Generation Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify JWT token and return decoded payload
     * 
     * @param string $token JWT token to verify
     * @return array|false Decoded payload on success, false on failure
     */
    public static function verifyToken($token) {
        if (empty($token)) {
            return false;
        }
        
        $secret = self::getSecretKey();
        
        try {
            $decoded = FirebaseJWT::decode($token, new Key($secret, self::$algorithm));
            
            // Convert stdClass to array
            $payload = (array) $decoded;
            
            // Verify required fields exist
            if (!isset($payload['user_id']) || !isset($payload['user_type'])) {
                return false;
            }
            
            return $payload;
        } catch (ExpiredException $e) {
            error_log("JWT Token Expired: " . $e->getMessage());
            return false;
        } catch (SignatureInvalidException $e) {
            error_log("JWT Invalid Signature: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("JWT Verification Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Refresh JWT token (generate new token from old one if not expired)
     * 
     * @param string $token Old JWT token
     * @param int $new_expiration Optional new expiration time
     * @return string|false New JWT token on success, false on failure
     */
    public static function refreshToken($token, $new_expiration = null) {
        $payload = self::verifyToken($token);
        
        if (!$payload) {
            return false;
        }
        
        // Create new payload from old one (update timestamps)
        $user_data = [
            'email' => $payload['email'] ?? null,
            'fname' => $payload['fname'] ?? null,
            'lname' => $payload['lname'] ?? null
        ];
        
        // Add user_id based on type
        switch ($payload['user_type']) {
            case 'super_admin':
                $user_data['super_admin_id'] = $payload['user_id'];
                break;
            case 'admin':
                $user_data['admin_id'] = $payload['user_id'];
                break;
            case 'bhw':
                $user_data['bhw_id'] = $payload['user_id'];
                break;
            case 'midwife':
                $user_data['midwife_id'] = $payload['user_id'];
                break;
            case 'user':
                $user_data['user_id'] = $payload['user_id'];
                break;
        }
        
        if (isset($payload['phone_number'])) {
            $user_data['phone_number'] = $payload['phone_number'];
        }
        
        if (isset($payload['profileimg'])) {
            $user_data['profileimg'] = $payload['profileimg'];
        }
        
        // Generate new token
        return self::generateToken($user_data, $payload['user_type'], $new_expiration);
    }
    
    /**
     * Extract JWT token from Authorization header or POST/GET request
     * 
     * @return string|false Token string or false if not found
     */
    public static function extractToken() {
        // Check Authorization header first (for API requests)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
            
            // Extract Bearer token
            if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                return trim($matches[1]);
            }
        }
        
        // Check for token in POST data
        if (isset($_POST['token'])) {
            return $_POST['token'];
        }
        
        // Check for token in GET data
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }
        
        // Check for token in custom header (for Flutter/apps)
        if (isset($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            return $_SERVER['HTTP_X_AUTH_TOKEN'];
        }
        
        return false;
    }
    
    /**
     * Set custom secret key (for configuration)
     */
    public static function setSecretKey($key) {
        global $jwt_secret_key;
        $jwt_secret_key = $key;
    }
    
    /**
     * Set custom expiration time
     */
    public static function setExpiration($seconds) {
        self::$expiration = $seconds;
    }
}
?>

