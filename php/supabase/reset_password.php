<?php
session_start();
include "../../database/SupabaseConfig.php";
include "../../database/DatabaseHelper.php";

header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Check if OTP was verified
if (!isset($_SESSION['reset_otp_verified']) || $_SESSION['reset_otp_verified'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'OTP verification required. Please verify your OTP first.']);
    exit();
}

// Get new password from POST data
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate passwords
if (empty($new_password) || empty($confirm_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Both password fields are required']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    exit();
}

// Password strength validation (same as create_account.php)
$password_errors = [];
if (strlen($new_password) < 8) {
    $password_errors[] = "Password must be at least 8 characters long.";
}
if (!preg_match('/[A-Z]/', $new_password)) {
    $password_errors[] = "Password must contain at least one uppercase letter.";
}
if (!preg_match('/[a-z]/', $new_password)) {
    $password_errors[] = "Password must contain at least one lowercase letter.";
}
if (!preg_match('/[0-9]/', $new_password)) {
    $password_errors[] = "Password must contain at least one number.";
}
if (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
    $password_errors[] = "Password must contain at least one special character.";
}

if (!empty($password_errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $password_errors)]);
    exit();
}

// Get user information from session
$user_id = $_SESSION['reset_verified_user_id'];
$user_table = $_SESSION['reset_verified_user_table'];

// Hash the new password with salt (exactly like in create_account.php)
$salt = bin2hex(random_bytes(32)); // 64 character hex string (same as create_account.php)
$hashed_password = password_hash($new_password . $salt, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    // Update password based on user table using Supabase
    $update_data = [
        'salt' => $salt,
        'updated' => date('Y-m-d H:i:s')
    ];
    
    if ($user_table === 'users') {
        $update_data['passw'] = $hashed_password;
        $result = supabaseUpdate('users', $update_data, ['user_id' => $user_id]);
    } elseif ($user_table === 'bhw') {
        $update_data['pass'] = $hashed_password;
        $result = supabaseUpdate('bhw', $update_data, ['bhw_id' => $user_id]);
    } elseif ($user_table === 'midwives') {
        $update_data['pass'] = $hashed_password;
        $result = supabaseUpdate('midwives', $update_data, ['midwife_id' => $user_id]);
    } elseif ($user_table === 'admin') {
        $update_data['pass'] = $hashed_password;
        $result = supabaseUpdate('admin', $update_data, ['admin_id' => $user_id]);
    } elseif ($user_table === 'super_admin') {
        $update_data['pass'] = $hashed_password;
        $result = supabaseUpdate('super_admin', $update_data, ['super_admin_id' => $user_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user type']);
        exit();
    }
    
    if ($result !== false) {
        // Clear reset session data
        unset($_SESSION['reset_otp_verified']);
        unset($_SESSION['reset_verified_user_id']);
        unset($_SESSION['reset_verified_user_table']);
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_user_table']);
        unset($_SESSION['reset_contact']);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Password reset successfully. You can now login with your new password.'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
    }
    
} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
}

// Clear sensitive data from memory
unset($new_password, $confirm_password, $hashed_password, $salt);
?>
