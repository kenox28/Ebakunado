<?php
session_start();
include_once "../../../database/SupabaseConfig.php";
include_once "../../../database/DatabaseHelper.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
	http_response_code(401);
	echo json_encode(["status" => "error", "message" => "Unauthorized"]);
	exit();
}

try {
	$locations = supabaseSelect('locations', '*', [], 'province.asc');
	echo json_encode($locations ?: []);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>


