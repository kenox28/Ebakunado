<?php
/**
 * Midwife Logout Handler
 * Handles midwife logout and session cleanup
 */

// Start output buffering to prevent any output before JSON
ob_start();

// Set JSON header first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Start session with error suppression to handle permission issues
@session_start();

require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';

// Get user info before clearing session
$midwife_id = $_SESSION['midwife_id'] ?? null;
$midwife_name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Log the logout activity
try {
    if ($midwife_id) {
        supabaseLogActivity($midwife_id, 'midwife', 'logout', 'Midwife logged out successfully: ' . $midwife_name, $ip);
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

// Clear output buffer and return success response
ob_clean();

echo json_encode([
    'status' => 'success',
    'message' => 'Midwife logged out successfully',
    'clear_token' => true // Signal to frontend to clear localStorage
]);
exit();
?>
