<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$user_id = $_SESSION['user_id'];
$baby_id = $_POST['baby_id'];
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

$own = supabaseSelect('child_health_records', 'id', ['baby_id' => $baby_id, 'user_id' => $_SESSION['user_id']], null, 1);
if (!$own || count($own) === 0) { echo json_encode(['status'=>'error','message'=>'Not found']); exit(); }

$columns = 'id,baby_id,vaccine_name,dose_number,status,schedule_date,date_given,catch_up_date,weight,height,temperature,created_at';
$rows = supabaseSelect('immunization_records', $columns, ['baby_id' => $baby_id], 'catch_up_date.asc');

echo json_encode(['status'=>'success','data'=>$rows ?: []]);



?>