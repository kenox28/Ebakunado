<?php
session_start();
header('Content-Type: application/json');

// Handle both BHW and Midwife sessions
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
if (!$user_id) { echo json_encode(['status'=>'error','message'=>'Unauthorized - User ID not found in session']); exit(); }

$_SESSION['bhw_read_notifications'] = ['ALL'];

echo json_encode(['status'=>'success']);
exit();
?>


