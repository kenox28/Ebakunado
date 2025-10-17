<?php

session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

$baby_id = $_POST['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

$ok = supabaseUpdate('child_health_records', ['status' => 'rejected'], ['baby_id' => $baby_id]);

echo json_encode(['status' => $ok !== false ? 'success' : 'error', 'message' => $ok !== false ? 'Record rejected' : 'Record not rejected']);

?>


