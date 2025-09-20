<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$user_id = $_SESSION['user_id'];

// Select child records for this user
$columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,date_created:date_created,date_updated:date_updated,status';
$rows = supabaseSelect('child_health_records', $columns, ['user_id' => $user_id], 'date_created.desc');

echo json_encode(['status'=>'success','data'=>$rows ?: []]);
?>


