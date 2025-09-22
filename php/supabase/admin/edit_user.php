<?php
session_start();
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

// Enable error reporting for debugging (match style but safe)
error_log("Edit user (Supabase): start");

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
	error_log("Edit user: Unauthorized access attempt");
	echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
	exit();
}

// Handle both GET (for fetching) and POST (for updating)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$user_id = $_GET['user_id'] ?? '';
} else {
	$user_id = $_POST['user_id'] ?? '';
}

error_log("Edit user: Attempting to edit user_id: '" . $user_id . "'");

if(empty($user_id)) {
	error_log("Edit user: Empty user_id provided");
	echo json_encode(array('status' => 'error', 'message' => 'User ID is required'));
	exit();
}

try {
	$rows = supabaseSelect('users', '*', ['user_id' => $user_id]);
	$found = $rows && count($rows) > 0;
	$log_count = $found ? count($rows) : 0;
	error_log("Edit user: Query executed, rows found: " . $log_count);

	if (!$found) {
		error_log("Edit user: No user found with ID: '" . $user_id . "'");
		echo json_encode(array('status' => 'error', 'message' => 'User not found'));
		exit();
	}

	$user = $rows[0];
	error_log("Edit user: Successfully retrieved user data for: " . $user['fname'] . ' ' . $user['lname']);
	echo json_encode(array('status' => 'success', 'data' => $user));

} catch (Exception $e) {
	error_log("Edit user: Exception occurred - " . $e->getMessage());
	echo json_encode(array('status' => 'error', 'message' => 'Database error: ' . $e->getMessage()));
}
?>


