<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$id = $_POST['id'] ?? '';
if ($id === '') { echo json_encode(['status'=>'error','message'=>'Missing id']); exit(); }

if (!isset($_SESSION['bhw_read_notifications']) || !is_array($_SESSION['bhw_read_notifications'])) {
    $_SESSION['bhw_read_notifications'] = [];
}
if (!in_array($id, $_SESSION['bhw_read_notifications'], true)) {
    $_SESSION['bhw_read_notifications'][] = $id;
}

echo json_encode(['status'=>'success']);
exit();
?>


