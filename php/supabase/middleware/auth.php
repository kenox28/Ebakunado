<?php
/**
 * JWT Authentication Middleware for Ebakunado
 * Checks JWT token or session and sets user data in $_SESSION for backward compatibility
 */

// Include JWT helper
require_once __DIR__ . '/JWT.php';

/**
 * Check JWT token or session and authenticate user
 * Sets $_SESSION variables from JWT payload for backward compatibility
 * 
 * @param bool $require_auth If true, exit with error if not authenticated
 * @param array $allowed_types Array of allowed user types (empty = all types)
 * @return array|false User payload on success, false on failure
 */
function authenticateUser($require_auth = true, $allowed_types = []) {
    // Try JWT token first
    $token = JWT::extractToken();
    $payload = null;
    
    if ($token) {
        $payload = JWT::verifyToken($token);
        
        if ($payload) {
            // Token is valid - set session variables from token for backward compatibility
            setSessionFromJWT($payload);
            return $payload;
        }
    }
    
    // Fallback to session-based auth (backward compatibility)
    $session_data = getSessionData();
    
    if ($session_data) {
        // Convert session data to payload format
        $payload = [
            'user_id' => $session_data['user_id'],
            'user_type' => $session_data['user_type'],
            'email' => $session_data['email'] ?? null,
            'fname' => $session_data['fname'] ?? null,
            'lname' => $session_data['lname'] ?? null
        ];
        
        return $payload;
    }
    
    // Not authenticated
    if ($require_auth) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Authentication required. Please login.'
        ]);
        exit();
    }
    
    return false;
}

/**
 * Set $_SESSION variables from JWT payload (for backward compatibility)
 */
function setSessionFromJWT($payload) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $user_type = $payload['user_type'];
    $user_id = $payload['user_id'];
    
    // Clear existing session data
    session_unset();
    
    // Set user ID based on type
    switch ($user_type) {
        case 'super_admin':
            $_SESSION['super_admin_id'] = $user_id;
            break;
        case 'admin':
            $_SESSION['admin_id'] = $user_id;
            break;
        case 'bhw':
            $_SESSION['bhw_id'] = $user_id;
            break;
        case 'midwife':
            $_SESSION['midwife_id'] = $user_id;
            break;
        case 'user':
            $_SESSION['user_id'] = $user_id;
            break;
    }
    
    // Set common session variables
    $_SESSION['user_type'] = $user_type;
    $_SESSION['email'] = $payload['email'] ?? null;
    $_SESSION['fname'] = $payload['fname'] ?? null;
    $_SESSION['lname'] = $payload['lname'] ?? null;
    $_SESSION['logged_in'] = true;
    
    // Set optional fields
    if (isset($payload['phone_number'])) {
        $_SESSION['phone_number'] = $payload['phone_number'];
    }
    
    if (isset($payload['profileimg'])) {
        $_SESSION['profileimg'] = $payload['profileimg'];
    }
}

/**
 * Get user data from session (backward compatibility helper)
 */
function getSessionData() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Check for different user types in session
    if (isset($_SESSION['super_admin_id'])) {
        return [
            'user_id' => $_SESSION['super_admin_id'],
            'user_type' => $_SESSION['user_type'] ?? 'super_admin',
            'email' => $_SESSION['email'] ?? null,
            'fname' => $_SESSION['fname'] ?? null,
            'lname' => $_SESSION['lname'] ?? null
        ];
    } elseif (isset($_SESSION['admin_id'])) {
        return [
            'user_id' => $_SESSION['admin_id'],
            'user_type' => $_SESSION['user_type'] ?? 'admin',
            'email' => $_SESSION['email'] ?? null,
            'fname' => $_SESSION['fname'] ?? null,
            'lname' => $_SESSION['lname'] ?? null
        ];
    } elseif (isset($_SESSION['bhw_id'])) {
        return [
            'user_id' => $_SESSION['bhw_id'],
            'user_type' => $_SESSION['user_type'] ?? 'bhw',
            'email' => $_SESSION['email'] ?? null,
            'fname' => $_SESSION['fname'] ?? null,
            'lname' => $_SESSION['lname'] ?? null
        ];
    } elseif (isset($_SESSION['midwife_id'])) {
        return [
            'user_id' => $_SESSION['midwife_id'],
            'user_type' => $_SESSION['user_type'] ?? 'midwife',
            'email' => $_SESSION['email'] ?? null,
            'fname' => $_SESSION['fname'] ?? null,
            'lname' => $_SESSION['lname'] ?? null
        ];
    } elseif (isset($_SESSION['user_id'])) {
        return [
            'user_id' => $_SESSION['user_id'],
            'user_type' => $_SESSION['user_type'] ?? 'user',
            'email' => $_SESSION['email'] ?? null,
            'fname' => $_SESSION['fname'] ?? null,
            'lname' => $_SESSION['lname'] ?? null
        ];
    }
    
    return false;
}

/**
 * Require specific user type(s)
 * 
 * @param string|array $required_types User type(s) required
 * @param bool $require_auth Require authentication
 */
function requireUserType($required_types, $require_auth = true) {
    $payload = authenticateUser($require_auth);
    
    if (!$payload) {
        return false;
    }
    
    $types = is_array($required_types) ? $required_types : [$required_types];
    
    if (!in_array($payload['user_type'], $types)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Insufficient permissions. Required: ' . implode(', ', $types)
        ]);
        exit();
    }
    
    return $payload;
}

/**
 * Check if user is authenticated (simple check)
 */
function isAuthenticated() {
    $token = JWT::extractToken();
    
    if ($token && JWT::verifyToken($token)) {
        return true;
    }
    
    return getSessionData() !== false;
}

/**
 * Get current authenticated user data
 */
function getCurrentUser() {
    $token = JWT::extractToken();
    
    if ($token) {
        $payload = JWT::verifyToken($token);
        if ($payload) {
            return $payload;
        }
    }
    
    return getSessionData();
}
?>

