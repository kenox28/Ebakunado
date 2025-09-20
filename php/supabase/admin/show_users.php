<?php
session_start();

// Check if admin or super admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
	http_response_code(401);
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

header('Content-Type: application/json');

try {
	$users = supabaseSelect('users', '*', [], 'created_at.desc');
	if ($users) {
		echo json_encode($users);
	} else {
		echo json_encode([]);
	}
} catch (Exception $e) {
	error_log("Show users error: " . $e->getMessage());
	echo json_encode([]);
}
?>


