<?php
session_start();
header('Content-Type: application/json');

// Check if super admin is logged in
if (!isset($_SESSION['super_admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../../database/Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = trim($_POST['admin_id'] ?? ''); // Remove whitespace

    if (empty($admin_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Admin ID is required']);
        exit();
    }

    try {
        // Check if admin exists
        $check_sql = "SELECT admin_id FROM admin WHERE TRIM(admin_id) = ?";
        $check_stmt = $connect->prepare($check_sql);
        $check_stmt->bind_param("s", $admin_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Admin not found']);
            exit();
        }

        // Delete admin
        $delete_sql = "DELETE FROM admin WHERE TRIM(admin_id) = ?";
        $stmt = $connect->prepare($delete_sql);
        $stmt->bind_param("s", $admin_id);
        
    if ($stmt->execute()) {
        // Log activity
        $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address) VALUES (?, 'super_admin', 'DELETE', ?, ?)";
        $log_stmt = $connect->prepare($log_sql);
        $description = "Deleted admin: " . $admin_id;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $log_stmt->bind_param("sss", $_SESSION['super_admin_id'], $description, $ip_address);
        $log_stmt->execute();
        
        echo json_encode(['status' => 'success', 'message' => 'Admin deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete admin']);
    }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$connect->close();
?>
