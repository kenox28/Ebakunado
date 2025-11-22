<?php
// Start output buffering to prevent any output before JSON
ob_start();

// Set JSON header first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Include database helper
include "../../../database/DatabaseHelper.php";


// Get user info before clearing session
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? 'bhw';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Log the logout activity
try {
    if ($user_id) {
        $description = ucfirst($user_type) . ' logged out successfully';
        supabaseLogActivity($user_id, $user_type, 'logout', $description, $ip);
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

// Clear and destroy the session
session_unset();
session_destroy();

// Clear output buffer and return success response
ob_clean();

// Determine the logout message based on user type
$logout_message = ucfirst($user_type) . " logged out successfully";

echo json_encode([
    "status" => "success",
    "message" => $logout_message,
    "clear_token" => true // Signal to frontend to clear localStorage
]);



?>
