<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database helper
include "../../../database/DatabaseHelper.php";

// Get user info before clearing session
$user_id = $_SESSION['super_admin_id'] ?? null;
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Log the logout activity
try {
    if ($user_id) {
        supabaseLogActivity($user_id, 'super_admin', 'logout', 'Super Admin logged out successfully', $ip);
    }
} catch (Exception $log_error) {
    error_log("Logout logging error: " . $log_error->getMessage());
    // Continue with logout even if logging fails
}

// Clear JWT token cookie
if (isset($_COOKIE['jwt_token'])) {
    setcookie('jwt_token', '', time() - 3600, '/');
    setcookie('jwt_token', '', time() - 3600, '/', '', false, true); // Also clear with secure flag
}

// Clear and destroy session
session_unset();
session_destroy();

echo json_encode([
    'status' => 'success', 
    'message' => 'Super Admin logged out successfully',
    'clear_token' => true // Signal to frontend to clear localStorage
]);
?>

