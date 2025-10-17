<?php
session_start();
header('Content-Type: application/json');

// Handle both BHW and Midwife sessions
$user_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? 'bhw';

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - User ID not found in session']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
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
        if ($user_type === 'midwife') {
            $userData = supabaseSelect('midwives', 'pass, salt', ['midwife_id' => $user_id]);
        } else {
            $userData = supabaseSelect('bhw', 'pass, salt', ['bhw_id' => $user_id]);
        }
        
        if ($userData && !empty($userData)) {
            // Unwrap first row if needed
            if (is_array($userData) && isset($userData[0])) { $userData = $userData[0]; }
            $storedHash = $userData['pass'] ?? '';
            $salt = $userData['salt'] ?? '';
            
            // Verify current password using bcrypt (same scheme as create account/login)
            if (!password_verify($current_password . $salt, $storedHash)) {
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
                exit();
            }
            
            // Hash new password using bcrypt with a fresh salt
            $newSalt = bin2hex(random_bytes(32));
            $newHash = password_hash($new_password . $newSalt, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $updateData['pass'] = $newHash;
            $updateData['salt'] = $newSalt;
        }
    }
    
    // Update user data
    if ($user_type === 'midwife') {
        $result = supabaseUpdate('midwives', $updateData, ['midwife_id' => $user_id]);
    } else {
        $result = supabaseUpdate('bhw', $updateData, ['bhw_id' => $user_id]);
    }
    
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
    error_log("Profile update error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}
?>
