<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) {
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

$columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,date_created,date_updated,status';
$rows = supabaseSelect('child_health_records', $columns, [], 'date_created.desc');

echo json_encode(['status' => 'success', 'data' => $rows ?: []]);
?>


