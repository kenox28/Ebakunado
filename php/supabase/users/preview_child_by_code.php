<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
	echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
	exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

$family_code = $_POST['family_code'] ?? '';
if ($family_code === '') {
	echo json_encode(['status' => 'error', 'message' => 'Family code is required']);
	exit();
}

$records = supabaseSelect('child_health_records', 'child_fname,child_lname,baby_id,status', ['user_id' => $family_code], null, 1);
if (!$records || count($records) === 0) {
	echo json_encode(['status' => 'error', 'message' => 'Invalid family code']);
	exit();
}

$record = $records[0];
$currentStatus = strtolower($record['status'] ?? '');
if ($currentStatus === 'accepted') {
	echo json_encode(['status' => 'error', 'message' => 'This child has already been claimed.']);
	exit();
}

$childName = trim(($record['child_fname'] ?? '') . ' ' . ($record['child_lname'] ?? ''));

echo json_encode([
	'status' => 'success',
	'child_name' => $childName,
	'baby_id' => $record['baby_id'] ?? '',
	'current_status' => $record['status'] ?? ''
]);

