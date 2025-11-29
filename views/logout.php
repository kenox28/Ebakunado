<?php
// Start output buffering to prevent any output before JSON
ob_start();

// Set JSON header first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Start session with error suppression to handle permission issues
@session_start();

// Get user info before clearing session
$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? 'user';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Log the logout activity if user was logged in
try {
    if ($user_id) {
        include "../database/Database.php";
        
        // Initialize database tables if they don't exist
        if (function_exists('initializeDatabase')) {
            initializeDatabase($connect);
        }
        
        // Log logout activity
        $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, 'user', 'logout', 'User logged out', ?, NOW())";
        $log_stmt = $connect->prepare($log_sql);
        if ($log_stmt) {
            $log_stmt->bind_param("ss", $user_id, $ip);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
} catch (Exception $log_error) {
    error_log("Logout logging error: " . $log_error->getMessage());
    // Continue with logout even if logging fails
}

// Clear JWT token cookie if exists
if (isset($_COOKIE['jwt_token'])) {
    setcookie('jwt_token', '', time() - 3600, '/');
    setcookie('jwt_token', '', time() - 3600, '/', '', false, true); // Also clear with secure flag
}

// Clear and destroy the session
session_unset();
session_destroy();

// Clear output buffer and return success response
ob_clean();

echo json_encode([
    "status" => "success",
    "message" => "User logged out successfully",
    "clear_token" => true // Signal to frontend to clear localStorage
]);
exit();
?> 