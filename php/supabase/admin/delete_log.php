<?php
session_start();
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

if(isset($_SESSION['admin_id']) || isset($_SESSION['super_admin_id'])) {
	$log_id = $_POST['log_id'] ?? '';
	
	if(empty($log_id)) {
		echo json_encode(array('status' => 'error', 'message' => 'Log ID is required'));
		exit();
	}
	
	try {
		$deleted = supabaseDelete('activity_logs', ['log_id' => intval($log_id)]);
		if ($deleted === false) {
			echo json_encode(array('status' => 'error', 'message' => 'Failed to delete log'));
			exit();
		}
		echo json_encode(array('status' => 'success', 'message' => 'Log deleted successfully'));
	} catch (Exception $e) {
		echo json_encode(array('status' => 'error', 'message' => 'Database error occurred'));
	}
} else {
	echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
}
?>


