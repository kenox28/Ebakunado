<?php
session_start();
include "../../../database/Database.php";

if(isset($_SESSION['admin_id']) || isset($_SESSION['super_admin_id'])) {
    $log_id = $_POST['log_id'] ?? '';
    
    if(empty($log_id)) {
        echo json_encode(array('status' => 'error', 'message' => 'Log ID is required'));
        exit();
    }
    
    try {
        $sql = "DELETE FROM activity_logs WHERE log_id = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $log_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(array('status' => 'success', 'message' => 'Log deleted successfully'));
    } catch (Exception $e) {
        echo json_encode(array('status' => 'error', 'message' => 'Database error occurred'));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
}
?>