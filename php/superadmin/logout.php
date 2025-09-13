<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to prevent breaking JSON
header('Content-Type: application/json');

session_start();

try {
    // Clean any output that might have been generated
    ob_clean();
    
    // Check if super admin session exists
    if (!isset($_SESSION['super_admin_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'No active session found']);
        exit();
    }

    // Include database connection for logging (optional)
    include '../../database/Database.php';
    
    // Log the logout activity (if connection is successful)
    if (isset($connect) && $connect) {
        try {
            $super_admin_id = $_SESSION['super_admin_id'];
            $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address) VALUES (?, ?, ?, ?, ?)";
            $log_stmt = $connect->prepare($log_sql);
            
            if ($log_stmt) {
                $user_type = 'super_admin';
                $action_type = 'logout';
                $description = 'Super Admin logged out';
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                
                $log_stmt->bind_param("sssss", $super_admin_id, $user_type, $action_type, $description, $ip_address);
                $log_stmt->execute();
            }
        } catch (Exception $log_error) {
            // If logging fails, continue with logout anyway
            // Don't break the logout process due to logging errors
        }
    }

    // Clear all session data
    session_unset();
    session_destroy();
    
    // Clear any remaining output
    ob_end_clean();
    
    // Return success response
    echo json_encode(['status' => 'success', 'message' => 'Super Admin logged out successfully']);
    
} catch (Exception $e) {
    // Clear any output and return error
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode(['status' => 'error', 'message' => 'Logout failed', 'debug' => ['error' => $e->getMessage()]]);
}

ob_end_flush();
?>
