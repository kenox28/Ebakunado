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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(["status" => "error", "message" => "Method not allowed"]);
	exit();
}

try {
	$location_id = intval($_POST['location_id'] ?? 0);

	if ($location_id <= 0) {
		throw new Exception("Invalid location ID");
	}

	// Get location details for logging
	$location = supabaseSelect('locations', 'province,city_municipality,barangay,purok', ['id' => $location_id]);
	if (!$location || count($location) === 0) {
		throw new Exception("Location not found");
	}
	$location_data = $location[0];

	// Delete location
	$deleted = supabaseDelete('locations', ['id' => $location_id]);
	if ($deleted === false) {
		throw new Exception("Failed to delete location");
	}

	// Log the activity
	$user_id = $_SESSION['admin_id'] ?? $_SESSION['super_admin_id'];
	$user_type = isset($_SESSION['admin_id']) ? 'admin' : 'super_admin';
	$action_type = 'DELETE_LOCATION';
	$description = "Deleted location: {$location_data['province']}, {$location_data['city_municipality']}, {$location_data['barangay']}, {$location_data['purok']}";
	$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

	supabaseLogActivity($user_id, $user_type, $action_type, $description, $ip_address);

	echo json_encode(["status" => "success", "message" => "Location deleted successfully"]);

} catch (Exception $e) {
	http_response_code(400);
	echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>


