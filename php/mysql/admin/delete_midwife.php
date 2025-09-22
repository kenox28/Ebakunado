<?php
session_start();
include "../../../database/Database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request method'));
    exit();
}

$midwife_id = $_POST['midwife_id'] ?? '';

if(empty($midwife_id)) {
    echo json_encode(array('status' => 'error', 'message' => 'Midwife ID is required'));
    exit();
}

try {
    $sql = "DELETE FROM midwives WHERE midwife_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $midwife_id);
    $result = $stmt->execute();
    
    if (!$result) {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to delete Midwife'));
        exit();
    }
    
    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Midwife not found'));
        exit();
    }
    
    $stmt->close();

    // Log the delete activity
    $ip = $_SERVER['REMOTE_ADDR'];
    $admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
    $admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
    
    $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, ?, 'midwife_delete', ?, ?, NOW())";
    $log_stmt = $connect->prepare($log_sql);
    $description = "Midwife deleted by admin: " . $admin_id;
    $log_stmt->bind_param("ssss", $midwife_id, $admin_type, $description, $ip);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode(array('status' => 'success', 'message' => 'Midwife deleted successfully'));

} catch (Exception $e) {
    error_log("Delete Midwife error: " . $e->getMessage());
    echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>
