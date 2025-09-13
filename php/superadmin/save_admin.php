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
    $admin_id = $_POST['admin_id'] ?? '';
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($admin_id) || empty($fname) || empty($lname) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
        exit();
    }

    // Password validation (same as create_account.php)
    if (strlen($password) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
        exit();
    }

    if (!preg_match('/[A-Z]/', $password)) {
        echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one uppercase letter']);
        exit();
    }

    if (!preg_match('/[a-z]/', $password)) {
        echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one lowercase letter']);
        exit();
    }

    if (!preg_match('/[0-9]/', $password)) {
        echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one number']);
        exit();
    }

    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one special character']);
        exit();
    }

    try {
        // Check if admin_id already exists (for new admins)
        if (!isset($_POST['admin_id']) || empty($_POST['admin_id'])) {
            // This is an edit, so admin_id should be provided
            echo json_encode(['status' => 'error', 'message' => 'Admin ID is required']);
            exit();
        }

        // Check if this is an update (admin exists) or insert (new admin)
        $check_sql = "SELECT admin_id FROM admin WHERE admin_id = ?";
        $check_stmt = $connect->prepare($check_sql);
        $check_stmt->bind_param("s", $admin_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing admin (without password change for now)
            $update_sql = "UPDATE admin SET fname = ?, lname = ?, email = ? WHERE admin_id = ?";
            $stmt = $connect->prepare($update_sql);
            $stmt->bind_param("ssss", $fname, $lname, $email, $admin_id);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Admin updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update admin']);
            }
        } else {
            // Check if email already exists
            $email_check_sql = "SELECT admin_id FROM admin WHERE email = ?";
            $email_stmt = $connect->prepare($email_check_sql);
            $email_stmt->bind_param("s", $email);
            $email_stmt->execute();
            $email_result = $email_stmt->get_result();
            
            if ($email_result->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
                exit();
            }

            // Insert new admin with hashed password
            $hashed_password = md5($password); // Using MD5 as per existing admin structure
            
            $insert_sql = "INSERT INTO admin (admin_id, fname, lname, email, pass, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $connect->prepare($insert_sql);
            $stmt->bind_param("sssss", $admin_id, $fname, $lname, $email, $hashed_password);
            
        if ($stmt->execute()) {
            // Log activity
            $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address) VALUES (?, 'super_admin', 'CREATE', ?, ?)";
            $log_stmt = $connect->prepare($log_sql);
            $description = "Created new admin: " . $admin_id;
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $log_stmt->bind_param("sss", $_SESSION['super_admin_id'], $description, $ip_address);
            $log_stmt->execute();
            
            echo json_encode(['status' => 'success', 'message' => 'Admin created successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create admin']);
        }
        }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$connect->close();
?>
