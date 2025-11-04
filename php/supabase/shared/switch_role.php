<?php
session_start();
header('Content-Type: application/json');

// Set proper error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, return as JSON

// Include database files with proper error handling
// From php/supabase/shared/, we need to go up 3 levels to reach root
$config_path = __DIR__ . '/../../../database/SupabaseConfig.php';
$helper_path = __DIR__ . '/../../../database/DatabaseHelper.php';

if (!file_exists($config_path)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database configuration file not found. Path: ' . $config_path
    ]);
    exit();
}

if (!file_exists($helper_path)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database helper file not found. Path: ' . $helper_path
    ]);
    exit();
}

include $config_path;
include $helper_path;

// Check if user is logged in
if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized. Only BHW and Midwife can switch to Parent role.'
    ]);
    exit();
}

// Get current user ID and email (store before session_unset)
$current_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$current_email = $_SESSION['email'] ?? null;
$current_phone = $_SESSION['phone_number'] ?? null;
$previous_role = isset($_SESSION['bhw_id']) ? 'bhw' : (isset($_SESSION['midwife_id']) ? 'midwife' : null);

if (empty($current_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User ID not found in session.'
    ]);
    exit();
}

try {
    // Check if user exists in users table
    $user_data = null;
    
    // Try by email first
    if (!empty($current_email)) {
        $rows = supabaseSelect('users', '*', ['email' => $current_email]);
        if ($rows && count($rows) > 0) {
            $user_data = $rows[0];
        }
    }
    
    // Try by phone if not found by email
    if (!$user_data && !empty($current_phone)) {
        $rows = supabaseSelect('users', '*', ['phone_number' => $current_phone]);
        if ($rows && count($rows) > 0) {
            $user_data = $rows[0];
        }
    }
    
    // Try by ID as last resort
    if (!$user_data) {
        $rows = supabaseSelect('users', '*', ['user_id' => $current_id]);
        if ($rows && count($rows) > 0) {
            $user_data = $rows[0];
        }
    }
    
    if (!$user_data) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User account not found in users table. Cannot switch to Parent role.'
        ]);
        exit();
    }
    
    // Switch session from BHW/Midwife to User
    session_unset();
    
    $_SESSION['user_id'] = $user_data['user_id'];
    $_SESSION['fname'] = $user_data['fname'];
    $_SESSION['lname'] = $user_data['lname'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['phone_number'] = $user_data['phone_number'] ?? null;
    $_SESSION['role'] = $user_data['role'] ?? 'user';
    $_SESSION['gender'] = $user_data['gender'] ?? null;
    $_SESSION['place'] = $user_data['place'] ?? null;
    $_SESSION['profileimg'] = $user_data['profileimg'] ?? $user_data['profileImg'] ?? 'noprofile.png';
    $_SESSION['user_type'] = 'user';
    $_SESSION['logged_in'] = true;
    
    // Store available roles (user might have multiple roles)
    $available_roles = [];
    if ($user_data) {
        $available_roles[] = 'user';
    }
    // Check if they also have BHW or Midwife role
    $bhw_check = supabaseSelect('bhw', '*', ['email' => $current_email]);
    if ($bhw_check && count($bhw_check) > 0) {
        $available_roles[] = 'bhw';
    }
    $midwife_check = supabaseSelect('midwives', '*', ['email' => $current_email]);
    if ($midwife_check && count($midwife_check) > 0) {
        $available_roles[] = 'midwife';
    }
    if (count($available_roles) > 1) {
        $_SESSION['available_roles'] = $available_roles;
    }
    
    // Log activity
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    supabaseLogActivity($user_data['user_id'], 'user', 'role_switch', 'User switched from ' . ucfirst($previous_role) . ' to Parent role', $ip);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Successfully switched to Parent role',
        'redirect_url' => '../../views/users/home.php'
    ]);
    
} catch (Exception $e) {
    error_log("Switch role error: " . $e->getMessage());
    error_log("Switch role trace: " . $e->getTraceAsString());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>

