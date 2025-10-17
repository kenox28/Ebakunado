<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Please log in first']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    $user_id = $_SESSION['user_id'];
    
    // Get form data
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $place = $_POST['place'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Prepare update data
    $updateData = [
        'fname' => $fname,
        'lname' => $lname,
        'email' => $email,
        'phone_number' => $phone_number,
        'gender' => $gender,
        'place' => $place,
        'updated' => date('c')
    ];
    
    // Handle password change if provided
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
            exit();
        }
        
        // Get current user data to verify current password
        $userData = supabaseSelect('users', 'passw, salt', ['user_id' => $user_id]);
        
        if ($userData && !empty($userData)) {
            $storedHash = $userData['passw'];
            $salt = $userData['salt'];
            
            // Verify current password using bcrypt
            if (!password_verify($current_password . $salt, $storedHash)) {
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
                exit();
            }
            
            // Hash new password with bcrypt
            $newSalt = bin2hex(random_bytes(32));
            $newHash = password_hash($new_password . $newSalt, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $updateData['passw'] = $newHash;
            $updateData['salt'] = $newSalt;
        }
    }
    
    // Update user data
    $result = supabaseUpdate('users', $updateData, ['user_id' => $user_id]);
    
    if ($result !== false) {
        // Update session data
        $_SESSION['fname'] = $fname;
        $_SESSION['lname'] = $lname;
        $_SESSION['email'] = $email;
        $_SESSION['phone_number'] = $phone_number;
        
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
    }
    
} catch (Exception $e) {
    error_log("User profile update error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}
?>
