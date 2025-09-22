<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

$baby_id = $_POST['baby_id'];
$columns = 'id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,date_created:date_created,date_updated:date_updated,status,qr_code';
$rows = supabaseSelect('child_health_records', $columns, ['baby_id' => $baby_id], 'date_created.desc');
$child_records = [];
if ($rows && count($rows) > 0) {
    foreach ($rows as $child) {
    $birth_date = new DateTime($child['child_birth_date']);
    $current_date = new DateTime();
    $weeks_old = $current_date->diff($birth_date)->days / 7;

    $age = $current_date->diff($birth_date)->y;


$child_records[] = [
    'id' => $child['id'],
    'baby_id' => $child['baby_id'],
    'name' => $child['child_fname'] . ' ' . $child['child_lname'],
    'age' => $age,
    'weeks_old' => round($weeks_old, 1),
    'gender' => $child['child_gender'],
    'status' => $child['status'],
    'qr_code' => $child['qr_code']
];
}
}

echo json_encode(['status'=>'success','data'=>$child_records ?: []]);



?>