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

$bhw_id = $_POST['bhw_id'] ?? '';

if(empty($bhw_id)) {
	echo json_encode(array('status' => 'error', 'message' => 'BHW ID is required'));
	exit();
}

try {
	$deleted = supabaseDelete('bhw', ['bhw_id' => $bhw_id]);
	if ($deleted === false) {
		echo json_encode(array('status' => 'error', 'message' => 'Failed to delete BHW'));
		exit();
	}

	if (!$deleted || count($deleted) === 0) {
		echo json_encode(array('status' => 'error', 'message' => 'BHW not found'));
		exit();
	}

	// Log the delete activity
	$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
	$admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
	$description = "BHW deleted by admin: " . $admin_id;
	supabaseLogActivity($bhw_id, $admin_type, 'bhw_delete', $description, $ip);

	echo json_encode(array('status' => 'success', 'message' => 'BHW deleted successfully'));

} catch (Exception $e) {
	error_log("Delete BHW error: " . $e->getMessage());
	echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>


