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
	// Use column alias so frontend gets `location_id`
	$columns = 'location_id:id,province,city_municipality,barangay,purok,created_at';
	$order = 'province.asc,city_municipality.asc,barangay.asc,purok.asc';
	$locations = supabaseSelect('locations', $columns, [], $order);
		echo json_encode($locations ?: []);
} catch (Exception $e) {
	error_log("Show places error: " . $e->getMessage());
	echo json_encode([]);
}
?>


