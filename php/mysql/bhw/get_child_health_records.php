<?php
session_start();
include '../../../database/Database.php';

header('Content-Type: application/json');

if ($connect->connect_error) {
	echo json_encode(['status' => 'error', 'message' => 'Connection Failed']);
	exit();
}

if (!isset($_SESSION['bhw_id'])) {
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

$sql = "SELECT id, user_id, baby_id, child_fname, child_lname, CONCAT(child_fname, ' ', child_lname) AS child_name, child_gender, child_birth_date, place_of_birth, mother_name, father_name, address, birth_weight, birth_height, birth_attendant, babys_card, date_created, date_updated, status FROM Child_Health_Records ORDER BY date_created DESC";
$res = mysqli_query($connect, $sql);

$list = [];
while ($row = mysqli_fetch_assoc($res)) {
	$list[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $list]);
