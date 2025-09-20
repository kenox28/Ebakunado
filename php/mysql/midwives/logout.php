<?php
// Start output buffering to prevent any output before JSON
ob_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser

// Set JSON header first
header('Content-Type: application/json');

try {
    session_start();
    
    // Try to include database - but don't fail if it doesn't work
    $db_connected = false;
    if (file_exists("../../database/Database.php")) {
        include "../../database/Database.php";
        if (isset($connect) && $connect) {
            $db_connected = true;
        }
    }

    $midwife_id = null;
    
    // Log the logout activity if user is logged in and DB is connected
    if (isset($_SESSION['midwife_id']) && $db_connected && isset($connect)) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $midwife_id = $_SESSION['midwife_id'];
            
            // Check if connection is valid
            if ($connect && !$connect->connect_error) {
                $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, ?, 'logout', ?, ?, NOW())";
                $log_stmt = $connect->prepare($log_sql);
                
                // Check if prepare was successful
                if ($log_stmt) {
                    $description = "Midwife logged out successfully";
                    $log_stmt->bind_param("ssss", $midwife_id, 'midwife', $description, $ip);
                    $log_stmt->execute();
                    $log_stmt->close();
                    error_log("Midwife logout logged successfully for ID: " . $midwife_id);
                } else {
                    error_log("Failed to prepare logout log statement: " . $connect->error);
                }
            } else {
                error_log("Database connection error during logout logging");
            }
        } catch (Exception $log_error) {
            error_log("Logout logging error: " . $log_error->getMessage());
            // Continue with logout even if logging fails
        } catch (Error $log_fatal) {
            error_log("Logout logging fatal error: " . $log_fatal->getMessage());
            // Continue with logout even if logging fails
        }
    }

    // Clear and destroy the session
    session_unset();
    session_destroy();

    // Clear output buffer and return success response
    ob_clean();
    echo json_encode([
        "status" => "success",
        "message" => "Midwife logged out successfully",
        "debug" => [
            "db_connected" => $db_connected,
            "had_session" => ($midwife_id !== null)
        ]
    ]);

} catch (Exception $e) {
    // Clear output buffer
    ob_clean();
    error_log("Logout error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Logout failed: " . $e->getMessage(),
        "debug" => [
            "error_line" => $e->getLine(),
            "error_file" => basename($e->getFile())
        ]
    ]);
} catch (Error $e) {
    // Handle fatal errors
    ob_clean();
    error_log("Logout fatal error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Logout failed: Fatal error occurred",
        "debug" => [
            "error_line" => $e->getLine(),
            "error_file" => basename($e->getFile())
        ]
    ]);
}

// End output buffering
ob_end_flush();
?>
