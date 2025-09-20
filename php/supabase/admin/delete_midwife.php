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

$midwife_id = $_POST['midwife_id'] ?? '';

if(empty($midwife_id)) {
	echo json_encode(array('status' => 'error', 'message' => 'Midwife ID is required'));
	exit();
}

try {
	$deleted = supabaseDelete('midwives', ['midwife_id' => $midwife_id]);
	if ($deleted === false) {
		echo json_encode(array('status' => 'error', 'message' => 'Failed to delete Midwife'));
		exit();
	}

	if (!$deleted || count($deleted) === 0) {
		echo json_encode(array('status' => 'error', 'message' => 'Midwife not found'));
		exit();
	}

	// Log the delete activity
	$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
	$admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
	$description = "Midwife deleted by admin: " . $admin_id;
	supabaseLogActivity($midwife_id, $admin_type, 'midwife_delete', $description, $ip);

	echo json_encode(array('status' => 'success', 'message' => 'Midwife deleted successfully'));

} catch (Exception $e) {
	error_log("Delete Midwife error: " . $e->getMessage());
	echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>


