<?php
/**
 * Mark All Notifications as Read for Midwives
 * Uses session-based tracking (same as Users and BHW)
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['midwife_id'])) { 
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); 
    exit(); 
}

$_SESSION['midwife_read_notifications'] = ['ALL'];

echo json_encode(['status'=>'success']);
exit();
?>
