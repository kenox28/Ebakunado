<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

// Mark all as read in-session for now
$_SESSION['read_notifications'] = ['ALL'];

echo json_encode(['status'=>'success']);
exit();
?>


