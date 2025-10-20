<?php
// Start output buffering to prevent any output before JSON
ob_start();


// Set JSON header first
header('Content-Type: application/json');

session_start();

// Try to include database - but don't fail if it doesn't work

include "../../../database/Database.php";


// Log the logout activity if user is logged in and DB is connected
try {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_id = $_SESSION['user_id'];
    
    // Check if connection is valid
    if ($connect && !$connect->connect_error) {
        $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, ?, 'logout', ?, ?, NOW())";
        $log_stmt = $connect->prepare($log_sql);
        
        // Check if prepare was successful
        if ($log_stmt) {
            $description = "User logged out successfully";
            $log_stmt->bind_param("ssss", $user_id, 'user', $description, $ip);
            $log_stmt->execute();
            $log_stmt->close();
            error_log("User logout logged successfully for ID: " . $user_id);
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

// Clear and destroy the session
session_unset();
session_destroy();

// Clear output buffer and return success response
ob_clean();
echo json_encode([
    "status" => "success",
    "message" => "User logged out successfully",
    "debug" => [
        "db_connected" => $db_connected,
        "had_session" => ($user_id !== null)
    ]
]);

?>