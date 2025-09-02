<?php
session_start();

// Log the logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    include "../database/Database.php";
    
    // Initialize database tables if they don't exist
    if (function_exists('initializeDatabase')) {
        initializeDatabase($connect);
    }
    
    // Log logout activity
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, 'user', 'logout', 'User logged out', ?, NOW())";
    $log_stmt = $connect->prepare($log_sql);
    $log_stmt->bind_param("ss", $_SESSION['user_id'], $ip);
    $log_stmt->execute();
    $log_stmt->close();
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to landing page
header("Location: landing_page.php");
exit();
?> 