<?php
session_start();
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
	echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
	exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(array('status' => 'error', 'message' => 'Invalid request method'));
	exit();
}

$user_id = $_POST['user_id'] ?? '';
if(empty($user_id)) {
	echo json_encode(array('status' => 'error', 'message' => 'User ID is required'));
	exit();
}

try {
	// Delete from users
	$deleted = supabaseDelete('users', ['user_id' => $user_id]);
	if ($deleted === false) {
		echo json_encode(array('status' => 'error', 'message' => 'Failed to delete user'));
		exit();
	}

	// Supabase returns representation; check if no rows returned
	if (!$deleted || count($deleted) === 0) {
		echo json_encode(array('status' => 'error', 'message' => 'User not found'));
		exit();
	}

	// Log the deletion activity
	$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
	$admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
	$description = "User deleted by admin: " . $admin_id;
	supabaseLogActivity($user_id, $admin_type, 'user_delete', $description, $ip);

	echo json_encode(array('status' => 'success', 'message' => 'User deleted successfully'));

} catch (Exception $e) {
	error_log("Delete user error: " . $e->getMessage());
	echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>


