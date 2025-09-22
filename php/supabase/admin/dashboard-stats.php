<?php
session_start();

// Check if admin or super admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
	http_response_code(401);
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

try {
	$users_count = supabaseCount('users');
	$bhws_count = supabaseCount('bhw');
	$midwives_count = supabaseCount('midwives');
	$locations_count = supabaseCount('locations');
	$logs_count = supabaseCount('activity_logs');

	$stats = [
		'users' => (int)$users_count,
		'bhws' => (int)$bhws_count,
		'midwives' => (int)$midwives_count,
		'locations' => (int)$locations_count,
		'activity_logs' => (int)$logs_count
	];

	echo json_encode([
		'status' => 'success',
		'stats' => $stats
	]);
	
} catch (Exception $e) {
	error_log("Dashboard stats error: " . $e->getMessage());
	echo json_encode([
		'status' => 'error',
		'message' => 'Failed to fetch dashboard statistics',
		'stats' => [
			'users' => 0,
			'bhws' => 0,
			'midwives' => 0,
			'locations' => 0,
			'activity_logs' => 0
		]
	]);
}
?>


