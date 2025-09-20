<?php
session_start();
header('Content-Type: application/json');

// Check if super admin is logged in
if (!isset($_SESSION['super_admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../../../database/Database.php';

// Check database connection
if (!$connect) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Handle both GET (for fetching data) and POST (for updating data)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['admin_id'])) {
    // Fetch admin data for editing
    $admin_id = trim($_GET['admin_id']); // Remove whitespace
    error_log("Edit admin GET request for admin_id: '" . $admin_id . "'");
    
    try {
        $sql = "SELECT admin_id, fname, lname, email, created_at FROM admin WHERE TRIM(admin_id) = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("s", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        error_log("Edit admin query executed, rows found: " . $result->num_rows);
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            error_log("Edit admin data retrieved: " . json_encode($admin));
            echo json_encode(['status' => 'success', 'data' => $admin]);
        } else {
            error_log("Edit admin: No admin found with ID: " . $admin_id);
            echo json_encode(['status' => 'error', 'message' => 'Admin not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update admin data
    $admin_id = trim($_POST['admin_id'] ?? ''); // Remove whitespace
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Validate required fields
    if (empty($admin_id) || empty($fname) || empty($lname) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
        exit();
    }
    
    try {
        // Check if email already exists for other admins
        $email_check_sql = "SELECT admin_id FROM admin WHERE email = ? AND TRIM(admin_id) != ?";
        $email_stmt = $connect->prepare($email_check_sql);
        $email_stmt->bind_param("ss", $email, $admin_id);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();
        
        if ($email_result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists for another admin']);
            exit();
        }
        
        // Update admin
        $update_sql = "UPDATE admin SET fname = ?, lname = ?, email = ?, updated_at = NOW() WHERE TRIM(admin_id) = ?";
        $stmt = $connect->prepare($update_sql);
        $stmt->bind_param("ssss", $fname, $lname, $email, $admin_id);
        
               if ($stmt->execute()) {
                   // Log activity
                   $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address) VALUES (?, 'super_admin', 'UPDATE', ?, ?)";
                   $log_stmt = $connect->prepare($log_sql);
                   $description = "Updated admin: " . $admin_id;
                   $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                   $log_stmt->bind_param("sss", $_SESSION['super_admin_id'], $description, $ip_address);
                   $log_stmt->execute();
                   
                   echo json_encode(['status' => 'success', 'message' => 'Admin updated successfully']);
               } else {
                   echo json_encode(['status' => 'error', 'message' => 'Failed to update admin']);
               }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

$connect->close();
?>
