<?php
session_start();
include '../../database/Database.php';
header('Content-Type: application/json');

if ($connect->connect_error) { echo json_encode(['status'=>'error','message'=>'DB error']); exit(); }
if (!isset($_SESSION['bhw_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$baby_id = $_GET['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

$sql = "SELECT id, baby_id, vaccine_name, dose_number, status, date_given, catch_up_date, weight, height, temperature, created_at FROM immunization_records WHERE baby_id = ? ORDER BY catch_up_date";
$stmt = $connect->prepare($sql);
$stmt->bind_param('s', $baby_id);
$stmt->execute();
$res = $stmt->get_result();
$list = [];
while ($row = $res->fetch_assoc()) { $list[] = $row; }
$stmt->close();

echo json_encode(['status'=>'success','data'=>$list]);
