<?php
session_start();
include "../../../database/Database.php";

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

$bhw_id = $_POST['bhw_id'] ?? '';
$fname = $_POST['fname'] ?? '';
$lname = $_POST['lname'] ?? '';
$email = $_POST['email'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$permissions = $_POST['permissions'] ?? '';
$gender = $_POST['gender'] ?? '';
$place = $_POST['place'] ?? '';

if(empty($bhw_id) || empty($fname) || empty($lname) || empty($email) || empty($phone_number)) {
    echo json_encode(array('status' => 'error', 'message' => 'BHW ID, first name, last name, email, and phone number are required'));
    exit();
}

try {
    $sql = "UPDATE bhw SET fname = ?, lname = ?, email = ?, phone_number = ?, permissions = ?, gender = ?, place = ?, updated = NOW() WHERE bhw_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssssssss", $fname, $lname, $email, $phone_number, $permissions, $gender, $place, $bhw_id);
    $result = $stmt->execute();
    
    if (!$result) {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to update BHW'));
        exit();
    }
    
    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        echo json_encode(array('status' => 'error', 'message' => 'BHW not found or no changes made'));
        exit();
    }
    
    $stmt->close();

    // Log the update activity
    $ip = $_SERVER['REMOTE_ADDR'];
    $admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
    $admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
    
    $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, ?, 'bhw_update', ?, ?, NOW())";
    $log_stmt = $connect->prepare($log_sql);
    $description = "BHW updated by admin: " . $admin_id;
    $log_stmt->bind_param("ssss", $bhw_id, $admin_type, $description, $ip);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode(array('status' => 'success', 'message' => 'BHW updated successfully'));

} catch (Exception $e) {
    error_log("Save BHW error: " . $e->getMessage());
    echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>
