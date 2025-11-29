<?php
/**
 * Restore PHP session from JWT token cookie
 * This allows users to stay logged in even after browser restart
 * 
 * @return bool True if session was restored, false otherwise
 */
function restore_session_from_jwt() {
    // If session already has user data, no need to restore
    if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return true;
    }
    
    // Check if JWT token exists in cookie
    if (!isset($_COOKIE['jwt_token']) || empty($_COOKIE['jwt_token'])) {
        return false;
    }
    
    // Load JWT class
    require_once __DIR__ . '/../JWT.php';
    
    try {
        // Verify and decode JWT token
        $payload = JWT::verifyToken($_COOKIE['jwt_token']);
        
        if (!$payload) {
            return false;
        }
        
        // Get user type from token
        $user_type = $payload['user_type'] ?? null;
        
        if (!$user_type) {
            return false;
        }
        
        // Restore session based on user type
        $_SESSION['user_type'] = $user_type;
        $_SESSION['logged_in'] = true;
        
        // Set user-specific session variables
        switch ($user_type) {
            case 'super_admin':
                $_SESSION['super_admin_id'] = $payload['user_id'];
                break;
            case 'admin':
                $_SESSION['admin_id'] = $payload['user_id'];
                break;
            case 'bhw':
                $_SESSION['bhw_id'] = $payload['user_id'];
                break;
            case 'midwife':
                $_SESSION['midwife_id'] = $payload['user_id'];
                break;
            case 'user':
                $_SESSION['user_id'] = $payload['user_id'];
                break;
        }
        
        // Set common session variables from token
        $_SESSION['email'] = $payload['email'] ?? null;
        $_SESSION['fname'] = $payload['fname'] ?? null;
        $_SESSION['lname'] = $payload['lname'] ?? null;
        $_SESSION['phone_number'] = $payload['phone_number'] ?? null;
        $_SESSION['profileimg'] = $payload['profileimg'] ?? $payload['profileImg'] ?? 'noprofile.png';
        $_SESSION['gender'] = $payload['gender'] ?? null;
        $_SESSION['place'] = $payload['place'] ?? null;
        
        return true;
        
    } catch (Exception $e) {
        // Invalid or expired token - clear cookie
        if (isset($_COOKIE['jwt_token'])) {
            setcookie('jwt_token', '', time() - 3600, '/');
        }
        return false;
    }
}

