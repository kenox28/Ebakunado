<?php
/**
 * Mark Notification as Read for Midwives
 * Uses session-based tracking (same as Users and BHW)
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['midwife_id'])) { 
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); 
    exit(); 
}

$id = $_POST['id'] ?? '';
if ($id === '') { 
    echo json_encode(['status'=>'error','message'=>'Missing id']); 
    exit(); 
}

if (!isset($_SESSION['midwife_read_notifications']) || !is_array($_SESSION['midwife_read_notifications'])) {
    $_SESSION['midwife_read_notifications'] = [];
}
if (!in_array($id, $_SESSION['midwife_read_notifications'], true)) {
    $_SESSION['midwife_read_notifications'][] = $id;
}

echo json_encode(['status'=>'success']);
exit();
?>
