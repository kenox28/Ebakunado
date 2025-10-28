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
    
    // Determine user ID and type (check both BHW and Midwife sessions)
    $user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
    $user_type = $_SESSION['user_type'] ?? null;
    
    // Determine user type string for description
    $description = "";
    $user_type_for_log = "";
    
    if (isset($_SESSION['midwife_id']) && $_SESSION['user_type'] === 'midwifes' || $_SESSION['user_type'] === 'midwife') {
        $description = "Midwife logged out successfully";
        $user_type_for_log = 'midwifes';
    } else {
        $description = "BHW logged out successfully";
        $user_type_for_log = 'bhw';
    }
    
    // Check if connection is valid
    if ($connect && !$connect->connect_error && $user_id) {
        $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, ?, 'logout', ?, ?, NOW())";
        $log_stmt = $connect->prepare($log_sql);
        
        // Check if prepare was successful
        if ($log_stmt) {
            $log_stmt->bind_param("ssss", $user_id, $user_type_for_log, $description, $ip);
            $log_stmt->execute();
            $log_stmt->close();
            error_log($description . " for ID: " . $user_id);
        } else {
            error_log("Failed to prepare logout log statement: " . $connect->error);
        }
    } else {
        error_log("Database connection error during logout logging or no user ID found");
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

// Determine the logout message based on user type
$logout_message = "User logged out successfully";
if (isset($_SESSION['midwife_id']) && $_SESSION['user_type'] === 'midwifes' || $_SESSION['user_type'] === 'midwife') {
    $logout_message = "Midwife logged out successfully";
} elseif (isset($_SESSION['bhw_id'])) {
    $logout_message = "BHW logged out successfully";
}

$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;

echo json_encode([
    "status" => "success",
    "message" => $logout_message,
    "debug" => [
        "db_connected" => $db_connected,
        "had_session" => ($user_id !== null)
    ]
]);



?>
