<?php
session_start();
include "../../../database/SupabaseConfig.php";
include "../../../database/DatabaseHelper.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
	echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
	exit();
}

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	echo json_encode(array('status' => 'error', 'message' => 'Invalid request method'));
	exit();
}

try {
	$midwives_data = supabaseSelect('midwives', '*', [], 'created_at.desc');
	echo json_encode($midwives_data ?: []);
} catch (Exception $e) {
	error_log("Show Midwives error: " . $e->getMessage());
	echo json_encode(array('status' => 'error', 'message' => 'Database error occurred'));
}
?>


