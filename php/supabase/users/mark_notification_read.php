<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

$id = $_POST['id'] ?? '';
if ($id === '') { echo json_encode(['status'=>'error','message'=>'Missing id']); exit(); }

if (!isset($_SESSION['read_notifications']) || !is_array($_SESSION['read_notifications'])) {
    $_SESSION['read_notifications'] = [];
}
if (!in_array($id, $_SESSION['read_notifications'], true)) {
    $_SESSION['read_notifications'][] = $id;
}

echo json_encode(['status'=>'success']);
exit();
?>


