<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database helper
include "../../../database/DatabaseHelper.php";

// Get user info before clearing session
$user_id = $_SESSION['admin_id'] ?? null;
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Log the logout activity
try {
    if ($user_id) {
        supabaseLogActivity($user_id, 'admin', 'logout', 'Admin logged out successfully', $ip);
    }
} catch (Exception $log_error) {
    error_log("Logout logging error: " . $log_error->getMessage());
    // Continue with logout even if logging fails
}

try {
	// Clear JWT token cookie
	if (isset($_COOKIE['jwt_token'])) {
		setcookie('jwt_token', '', time() - 3600, '/');
		setcookie('jwt_token', '', time() - 3600, '/', '', false, true); // Also clear with secure flag
	}
	
	// Clear session
	$_SESSION = array();
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	session_destroy();
	
	echo json_encode([
		'status' => 'success', 
		'message' => 'Admin logged out successfully',
		'clear_token' => true // Signal to frontend to clear localStorage
	]);
} catch (Exception $e) {
	echo json_encode(['status' => 'error', 'message' => 'Logout failed']);
}
?>

