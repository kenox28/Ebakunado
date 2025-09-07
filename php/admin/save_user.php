<?php
session_start();
include "../../database/Database.php";

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

$user_id = $_POST['user_id'] ?? '';
$fname = $_POST['fname'] ?? '';
$lname = $_POST['lname'] ?? '';
$email = $_POST['email'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$role = $_POST['role'] ?? '';

if(empty($user_id) || empty($fname) || empty($lname) || empty($email) || empty($phone_number)) {
    echo json_encode(array('status' => 'error', 'message' => 'User ID, first name, last name, email, and phone number are required'));
    exit();
}

try {
    $sql = "UPDATE users SET fname = ?, lname = ?, email = ?, phone_number = ?, role = ? WHERE user_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssssss", $fname, $lname, $email, $phone_number, $role, $user_id);
    $result = $stmt->execute();
    
    if (!$result) {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to update user'));
        exit();
    }
    
    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        echo json_encode(array('status' => 'error', 'message' => 'User not found or no changes made'));
        exit();
    }
    
    $stmt->close();

    // Check if role was changed to bhw or midwife
    if ($role === 'bhw' || $role === 'midwife') {
        // Get the updated user data
        $get_user_sql = "SELECT * FROM users WHERE user_id = ?";
        $get_user_stmt = $connect->prepare($get_user_sql);
        $get_user_stmt->bind_param("s", $user_id);
        $get_user_stmt->execute();
        $user_result = $get_user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $get_user_stmt->close();
        
        if ($user_data) {
            if ($role === 'bhw') {
                // Check if user already exists in bhw table
                $check_bhw = "SELECT * FROM bhw WHERE bhw_id = ?";
                $check_stmt = $connect->prepare($check_bhw);
                $check_stmt->bind_param("s", $user_id);
                $check_stmt->execute();
                $bhw_exists = $check_stmt->get_result()->num_rows > 0;
                $check_stmt->close();
                
                if (!$bhw_exists) {
                    // Insert into bhw table
                    $insert_bhw = "INSERT INTO bhw (bhw_id, fname, lname, email, pass, phone_number, salt, profileImg, gender, bdate, permissions, role, created_at, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'view', 'bhw', NOW(), NOW())";
                    $bhw_stmt = $connect->prepare($insert_bhw);
                    $bhw_stmt->bind_param("ssssssssss", 
                        $user_data['user_id'], 
                        $user_data['fname'], 
                        $user_data['lname'], 
                        $user_data['email'], 
                        $user_data['passw'], 
                        $user_data['phone_number'], 
                        $user_data['salt'], 
                        $user_data['profileImg'], 
                        $user_data['gender'], 
                        $user_data['bdate']
                    );
                    $bhw_stmt->execute();
                    $bhw_stmt->close();
                }
            } 
            elseif ($role === 'midwife') {
                // Check if user already exists in midwives table
                $check_midwife = "SELECT * FROM midwives WHERE midwife_id = ?";
                $check_stmt = $connect->prepare($check_midwife);
                $check_stmt->bind_param("s", $user_id);
                $check_stmt->execute();
                $midwife_exists = $check_stmt->get_result()->num_rows > 0;
                $check_stmt->close();
                
                if (!$midwife_exists) {
                    // Insert into midwives table
                    $insert_midwife = "INSERT INTO midwives (midwife_id, fname, lname, email, pass, phone_number, salt, profileImg, gender, bdate, permissions, Approve, role, created_at, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'view', 0, 'midwife', NOW(), NOW())";
                    $midwife_stmt = $connect->prepare($insert_midwife);
                    $midwife_stmt->bind_param("ssssssssss", 
                        $user_data['user_id'], 
                        $user_data['fname'], 
                        $user_data['lname'], 
                        $user_data['email'], 
                        $user_data['passw'], 
                        $user_data['phone_number'], 
                        $user_data['salt'], 
                        $user_data['profileImg'], 
                        $user_data['gender'], 
                        $user_data['bdate']
                    );
                    $midwife_stmt->execute();
                    $midwife_stmt->close();
                }
            }
        }
    }

    // Log the update activity
    $ip = $_SERVER['REMOTE_ADDR'];
    $admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
    $admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
    
    $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, ?, 'user_update', ?, ?, NOW())";
    $log_stmt = $connect->prepare($log_sql);
    $description = "User updated by admin: " . $admin_id;
    $log_stmt->bind_param("ssss", $user_id, $admin_type, $description, $ip);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode(array('status' => 'success', 'message' => 'User updated successfully'));

} catch (Exception $e) {
    error_log("Save user error: " . $e->getMessage());
    echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>
