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
    // Start transaction
    $connect->autocommit(false);
    
    // Get current user data before any changes
    $get_current_sql = "SELECT * FROM users WHERE user_id = ?";
    $get_current_stmt = $connect->prepare($get_current_sql);
    $get_current_stmt->bind_param("s", $user_id);
    $get_current_stmt->execute();
    $current_result = $get_current_stmt->get_result();
    $current_user = $current_result->fetch_assoc();
    $get_current_stmt->close();
    
    if (!$current_user) {
        // Check if user exists in BHW table
        $check_bhw_sql = "SELECT * FROM bhw WHERE bhw_id = ?";
        $check_bhw_stmt = $connect->prepare($check_bhw_sql);
        $check_bhw_stmt->bind_param("s", $user_id);
        $check_bhw_stmt->execute();
        $bhw_result = $check_bhw_stmt->get_result();
        $current_user = $bhw_result->fetch_assoc();
        $check_bhw_stmt->close();
        
        if ($current_user) {
            $current_user['current_table'] = 'bhw';
        } else {
            // Check if user exists in Midwives table
            $check_midwife_sql = "SELECT * FROM midwives WHERE midwife_id = ?";
            $check_midwife_stmt = $connect->prepare($check_midwife_sql);
            $check_midwife_stmt->bind_param("s", $user_id);
            $check_midwife_stmt->execute();
            $midwife_result = $check_midwife_stmt->get_result();
            $current_user = $midwife_result->fetch_assoc();
            $check_midwife_stmt->close();
            
            if ($current_user) {
                $current_user['current_table'] = 'midwives';
            } else {
                $connect->rollback();
                echo json_encode(array('status' => 'error', 'message' => 'User not found'));
                exit();
            }
        }
    } else {
        $current_user['current_table'] = 'users';
    }
    
    $current_table = $current_user['current_table'];
    $current_role = $current_user['role'] ?? 'user';
    
    // Handle role changes
    if ($role !== $current_role) {
        // Moving TO users table (bhw/midwife → user)
        if ($role === 'user') {
            if ($current_table === 'bhw') {
                // Move from BHW to users
                $insert_user = "INSERT INTO users (user_id, fname, lname, email, passw, phone_number, salt, profileImg, gender, place, role, created_at, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', NOW(), NOW())";
                $user_stmt = $connect->prepare($insert_user);
                $user_stmt->bind_param("ssssssssss", 
                    $current_user['bhw_id'], 
                    $fname, 
                    $lname, 
                    $email, 
                    $current_user['pass'], 
                    $phone_number, 
                    $current_user['salt'], 
                    $current_user['profileImg'], 
                    $current_user['gender'], 
                    $current_user['place']
                );
                
                if (!$user_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to move user from BHW to users table'));
                    exit();
                }
                $user_stmt->close();
                
                // Delete from BHW table
                $delete_bhw = "DELETE FROM bhw WHERE bhw_id = ?";
                $delete_stmt = $connect->prepare($delete_bhw);
                $delete_stmt->bind_param("s", $user_id);
                if (!$delete_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to remove user from BHW table'));
                    exit();
                }
                $delete_stmt->close();
                
            } elseif ($current_table === 'midwives') {
                // Move from Midwives to users
                $insert_user = "INSERT INTO users (user_id, fname, lname, email, passw, phone_number, salt, profileImg, gender, place, role, created_at, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', NOW(), NOW())";
                $user_stmt = $connect->prepare($insert_user);
                $user_stmt->bind_param("ssssssssss", 
                    $current_user['midwife_id'], 
                    $fname, 
                    $lname, 
                    $email, 
                    $current_user['pass'], 
                    $phone_number, 
                    $current_user['salt'], 
                    $current_user['profileImg'], 
                    $current_user['gender'], 
                    $current_user['place']
                );
                
                if (!$user_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to move user from Midwives to users table'));
                    exit();
                }
                $user_stmt->close();
                
                // Delete from Midwives table
                $delete_midwife = "DELETE FROM midwives WHERE midwife_id = ?";
                $delete_stmt = $connect->prepare($delete_midwife);
                $delete_stmt->bind_param("s", $user_id);
                if (!$delete_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to remove user from Midwives table'));
                    exit();
                }
                $delete_stmt->close();
            } else {
                // Just update in users table
                $update_user = "UPDATE users SET fname = ?, lname = ?, email = ?, phone_number = ?, role = ? WHERE user_id = ?";
                $update_stmt = $connect->prepare($update_user);
                $update_stmt->bind_param("ssssss", $fname, $lname, $email, $phone_number, $role, $user_id);
                if (!$update_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to update user'));
                    exit();
                }
                $update_stmt->close();
            }
        }
        // Moving TO bhw table
        elseif ($role === 'bhw') {
            if ($current_table === 'users') {
                // Move from users to BHW
                $insert_bhw = "INSERT INTO bhw (bhw_id, fname, lname, email, pass, phone_number, salt, profileImg, gender, place, permissions, role, created_at, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'view', 'bhw', NOW(), NOW())";
                $bhw_stmt = $connect->prepare($insert_bhw);
                $bhw_stmt->bind_param("ssssssssss", 
                    $current_user['user_id'], 
                    $fname, 
                    $lname, 
                    $email, 
                    $current_user['passw'], 
                    $phone_number, 
                    $current_user['salt'], 
                    $current_user['profileImg'], 
                    $current_user['gender'], 
                    $current_user['place']
                );
                
                if (!$bhw_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to move user to BHW table'));
                    exit();
                }
                $bhw_stmt->close();
                
                // Delete from users table
                $delete_user = "DELETE FROM users WHERE user_id = ?";
                $delete_stmt = $connect->prepare($delete_user);
                $delete_stmt->bind_param("s", $user_id);
                if (!$delete_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to remove user from users table'));
                    exit();
                }
                $delete_stmt->close();
                
            } elseif ($current_table === 'midwives') {
                // Move from midwives to BHW
                $insert_bhw = "INSERT INTO bhw (bhw_id, fname, lname, email, pass, phone_number, salt, profileImg, gender, place, permissions, role, created_at, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'view', 'bhw', NOW(), NOW())";
                $bhw_stmt = $connect->prepare($insert_bhw);
                $bhw_stmt->bind_param("ssssssssss", 
                    $current_user['midwife_id'], 
                    $fname, 
                    $lname, 
                    $email, 
                    $current_user['pass'], 
                    $phone_number, 
                    $current_user['salt'], 
                    $current_user['profileImg'], 
                    $current_user['gender'], 
                    $current_user['place']
                );
                
                if (!$bhw_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to move user to BHW table'));
                    exit();
                }
                $bhw_stmt->close();
                
                // Delete from midwives table
                $delete_midwife = "DELETE FROM midwives WHERE midwife_id = ?";
                $delete_stmt = $connect->prepare($delete_midwife);
                $delete_stmt->bind_param("s", $user_id);
                if (!$delete_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to remove user from midwives table'));
                    exit();
                }
                $delete_stmt->close();
            } else {
                // Just update in BHW table
                $update_bhw = "UPDATE bhw SET fname = ?, lname = ?, email = ?, phone_number = ? WHERE bhw_id = ?";
                $update_stmt = $connect->prepare($update_bhw);
                $update_stmt->bind_param("sssss", $fname, $lname, $email, $phone_number, $user_id);
                if (!$update_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to update BHW'));
                    exit();
                }
                $update_stmt->close();
            }
        }
        // Moving TO midwives table
        elseif ($role === 'midwife') {
            if ($current_table === 'users') {
                // Move from users to midwives
                $insert_midwife = "INSERT INTO midwives (midwife_id, fname, lname, email, pass, phone_number, salt, profileImg, gender, place, permissions, Approve, role, created_at, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'view', 0, 'midwife', NOW(), NOW())";
                $midwife_stmt = $connect->prepare($insert_midwife);
                $midwife_stmt->bind_param("ssssssssss", 
                    $current_user['user_id'], 
                    $fname, 
                    $lname, 
                    $email, 
                    $current_user['passw'], 
                    $phone_number, 
                    $current_user['salt'], 
                    $current_user['profileImg'], 
                    $current_user['gender'], 
                    $current_user['place']
                );
                
                if (!$midwife_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to move user to midwives table'));
                    exit();
                }
                $midwife_stmt->close();
                
                // Delete from users table
                $delete_user = "DELETE FROM users WHERE user_id = ?";
                $delete_stmt = $connect->prepare($delete_user);
                $delete_stmt->bind_param("s", $user_id);
                if (!$delete_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to remove user from users table'));
                    exit();
                }
                $delete_stmt->close();
                
            } elseif ($current_table === 'bhw') {
                // Move from BHW to midwives
                $insert_midwife = "INSERT INTO midwives (midwife_id, fname, lname, email, pass, phone_number, salt, profileImg, gender, place, permissions, Approve, role, created_at, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'view', 0, 'midwife', NOW(), NOW())";
                $midwife_stmt = $connect->prepare($insert_midwife);
                $midwife_stmt->bind_param("ssssssssss", 
                    $current_user['bhw_id'], 
                    $fname, 
                    $lname, 
                    $email, 
                    $current_user['pass'], 
                    $phone_number, 
                    $current_user['salt'], 
                    $current_user['profileImg'], 
                    $current_user['gender'], 
                    $current_user['place']
                );
                
                if (!$midwife_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to move user to midwives table'));
                    exit();
                }
                $midwife_stmt->close();
                
                // Delete from BHW table
                $delete_bhw = "DELETE FROM bhw WHERE bhw_id = ?";
                $delete_stmt = $connect->prepare($delete_bhw);
                $delete_stmt->bind_param("s", $user_id);
                if (!$delete_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to remove user from BHW table'));
                    exit();
                }
                $delete_stmt->close();
            } else {
                // Just update in midwives table
                $update_midwife = "UPDATE midwives SET fname = ?, lname = ?, email = ?, phone_number = ? WHERE midwife_id = ?";
                $update_stmt = $connect->prepare($update_midwife);
                $update_stmt->bind_param("sssss", $fname, $lname, $email, $phone_number, $user_id);
                if (!$update_stmt->execute()) {
                    $connect->rollback();
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to update midwife'));
                    exit();
                }
                $update_stmt->close();
            }
        }
    } else {
        // No role change, just update in current table
        if ($current_table === 'users') {
            $update_user = "UPDATE users SET fname = ?, lname = ?, email = ?, phone_number = ? WHERE user_id = ?";
            $update_stmt = $connect->prepare($update_user);
            $update_stmt->bind_param("sssss", $fname, $lname, $email, $phone_number, $user_id);
        } elseif ($current_table === 'bhw') {
            $update_user = "UPDATE bhw SET fname = ?, lname = ?, email = ?, phone_number = ? WHERE bhw_id = ?";
            $update_stmt = $connect->prepare($update_user);
            $update_stmt->bind_param("sssss", $fname, $lname, $email, $phone_number, $user_id);
        } else {
            $update_user = "UPDATE midwives SET fname = ?, lname = ?, email = ?, phone_number = ? WHERE midwife_id = ?";
            $update_stmt = $connect->prepare($update_user);
            $update_stmt->bind_param("sssss", $fname, $lname, $email, $phone_number, $user_id);
        }
        
        if (!$update_stmt->execute()) {
            $connect->rollback();
            echo json_encode(array('status' => 'error', 'message' => 'Failed to update user'));
            exit();
        }
        $update_stmt->close();
    }

    // Log the update activity
    $ip = $_SERVER['REMOTE_ADDR'];
    $admin_type = isset($_SESSION['super_admin_id']) ? 'super_admin' : 'admin';
    $admin_id = isset($_SESSION['super_admin_id']) ? $_SESSION['super_admin_id'] : $_SESSION['admin_id'];
    
    $log_sql = "INSERT INTO activity_logs (user_id, user_type, action_type, description, ip_address, created_at) VALUES (?, ?, 'user_update', ?, ?, NOW())";
    $log_stmt = $connect->prepare($log_sql);
    $description = "User role changed by admin: " . $admin_id . " from " . $current_role . " to " . $role;
    $log_stmt->bind_param("ssss", $user_id, $admin_type, $description, $ip);
    $log_stmt->execute();
    $log_stmt->close();

    // Commit the transaction
    $connect->commit();
    $connect->autocommit(true);

    echo json_encode(array('status' => 'success', 'message' => 'User updated successfully'));

} catch (Exception $e) {
    // Rollback transaction on error
    if ($connect) {
        $connect->rollback();
        $connect->autocommit(true);
    }
    error_log("Save user error: " . $e->getMessage());
    echo json_encode(array('status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()));
}
?>
