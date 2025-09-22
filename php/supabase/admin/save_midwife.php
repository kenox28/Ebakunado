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
$fname = $_POST['fname'] ?? '';
$lname = $_POST['lname'] ?? '';
$email = $_POST['email'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$permissions = $_POST['permissions'] ?? '';
$approve = $_POST['approve'] ?? '0';
$gender = $_POST['gender'] ?? '';
$place = $_POST['place'] ?? '';

if(empty($midwife_id) || empty($fname) || empty($lname) || empty($email) || empty($phone_number)) {
	echo json_encode(array('status' => 'error', 'message' => 'Midwife ID, first name, last name, email, and phone number are required'));
	exit();
}

try {
	$update = supabaseUpdate('midwives', [
		'fname' => $fname,
		'lname' => $lname,
		'email' => $email,
		'phone_number' => $phone_number,
		'permissions' => $permissions,
		'Approve' => $approve,
		'gender' => $gender,
		'place' => $place,
		'updated' => date('c')
	], ['midwife_id' => $midwife_id]);

	if ($update === false) {
		echo json_encode(array('status' => 'error', 'message' => 'Failed to update Midwife'));
		exit();
	}

	if (!$update || count($update) === 0) {
		echo json_encode(array('status' => 'error', 'message' => 'Midwife not found or no changes made'));
		exit();
	}

	// Log the update activity
	$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
	$admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
	$description = "Midwife updated by admin: " . $admin_id;
	supabaseLogActivity($midwife_id, $admin_type, 'midwife_update', $description, $ip);

	echo json_encode(array('status' => 'success', 'message' => 'Midwife updated successfully'));

} catch (Exception $e) {
	error_log("Save Midwife error: " . $e->getMessage());
	echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>


