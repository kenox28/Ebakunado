<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$baby_id = $_GET['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

$columns = 'id,baby_id,vaccine_name,dose_number,status,date_given,catch_up_date,weight,height,temperature,created_at';
$rows = supabaseSelect('immunization_records', $columns, ['baby_id' => $baby_id], 'catch_up_date.asc');

echo json_encode(['status'=>'success','data'=>$rows ?: []]);
?>


