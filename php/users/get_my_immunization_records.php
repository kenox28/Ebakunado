<?php
session_start();
include '../../database/Database.php';
header('Content-Type: application/json');

if (!$connect) { echo json_encode(['status'=>'error','message'=>'DB error']); exit(); }
if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$baby_id = $_GET['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

// Ensure the baby belongs to the logged in user
$own_sql = "SELECT 1 FROM Child_Health_Records WHERE baby_id = ? AND user_id = ? LIMIT 1";
$own_stmt = $connect->prepare($own_sql);
$own_stmt->bind_param('ss', $baby_id, $_SESSION['user_id']);
$own_stmt->execute();
$own_res = $own_stmt->get_result();
if ($own_res->num_rows === 0) { echo json_encode(['status'=>'error','message'=>'Not found']); exit(); }
$own_stmt->close();

$sql = "SELECT id, baby_id, vaccine_name, dose_number, status, date_given, catch_up_date, weight, height, temperature, created_at FROM immunization_records WHERE baby_id = ? ORDER BY catch_up_date";
$stmt = $connect->prepare($sql);
$stmt->bind_param('s', $baby_id);
$stmt->execute();
$res = $stmt->get_result();
$list = [];
while ($row = $res->fetch_assoc()) { $list[] = $row; }
$stmt->close();

echo json_encode(['status'=>'success','data'=>$list]);
?>

