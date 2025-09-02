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
