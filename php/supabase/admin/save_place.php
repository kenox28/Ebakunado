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
	$province = trim($_POST['province'] ?? '');
	$city_municipality = trim($_POST['city_municipality'] ?? '');
	$barangay = trim($_POST['barangay'] ?? '');
	$purok = trim($_POST['purok'] ?? '');

	if (empty($province) || empty($city_municipality) || empty($barangay) || empty($purok)) {
		throw new Exception("All location fields are required");
	}

	$existing = supabaseSelect('locations', 'id', [
		'province' => $province,
		'city_municipality' => $city_municipality,
		'barangay' => $barangay,
		'purok' => $purok
	]);

	if ($existing && count($existing) > 0) {
		throw new Exception("This location already exists");
	}

	$insert = supabaseInsert('locations', [
		'province' => $province,
		'city_municipality' => $city_municipality,
		'barangay' => $barangay,
		'purok' => $purok
	]);

	if ($insert === false) {
		throw new Exception("Failed to add location");
	}

	$user_id = $_SESSION['admin_id'] ?? $_SESSION['super_admin_id'];
	$user_type = isset($_SESSION['admin_id']) ? 'admin' : 'super_admin';
	$action_type = 'ADD_LOCATION';
	$description = "Added new location: $province, $city_municipality, $barangay, $purok";
	$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

	supabaseLogActivity($user_id, $user_type, $action_type, $description, $ip_address);

	echo json_encode(["status" => "success", "message" => "Location added successfully"]);

} catch (Exception $e) {
	http_response_code(400);
	echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>


