<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$_SESSION['bhw_read_notifications'] = ['ALL'];

echo json_encode(['status'=>'success']);
exit();
?>


