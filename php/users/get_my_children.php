<?php
session_start();
include '../../database/Database.php';
header('Content-Type: application/json');

if (!$connect) { echo json_encode(['status'=>'error','message'=>'DB error']); exit(); }
if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$user_id = $_SESSION['user_id'];

$sql = "SELECT id, user_id, baby_id, child_fname, child_lname, CONCAT(child_fname,' ',child_lname) AS child_name, child_gender, child_birth_date, place_of_birth, mother_name, father_name, address, birth_weight, birth_height, birth_attendant, babys_card, date_created, date_updated, status FROM Child_Health_Records WHERE user_id = ? ORDER BY date_created DESC";
$stmt = $connect->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$list = [];
while ($row = $res->fetch_assoc()) { $list[] = $row; }
$stmt->close();

echo json_encode(['status'=>'success','data'=>$list]);
?>

