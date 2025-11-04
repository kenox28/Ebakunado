<?php
session_start();
header('Content-Type: application/json');

// Set proper error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, return as JSON

// Include database files with proper error handling
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

// Check if user is logged in as User
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized. Only logged-in users can switch to BHW/Midwife role.'
    ]);
    exit();
}

// Get current user email (store before session_unset)
$current_email = $_SESSION['email'] ?? null;
$current_phone = $_SESSION['phone_number'] ?? null;

if (empty($current_email) && empty($current_phone)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User email/phone not found in session.'
    ]);
    exit();
}

try {
    // Determine which role to switch back to (priority: bhw > midwife)
    $target_role = null;
    $target_data = null;
    
    // Check available roles from session
    if (isset($_SESSION['available_roles'])) {
        if (in_array('bhw', $_SESSION['available_roles'])) {
            $target_role = 'bhw';
        } elseif (in_array('midwife', $_SESSION['available_roles'])) {
            $target_role = 'midwife';
        }
    }
    
    // If not in session, check database
    if (!$target_role) {
        // Check bhw first
        if (!empty($current_email)) {
            $rows = supabaseSelect('bhw', '*', ['email' => $current_email]);
            if ($rows && count($rows) > 0) {
                $target_data = $rows[0];
                $target_role = 'bhw';
            }
        }
        
        // Check midwife if bhw not found
        if (!$target_data && !empty($current_email)) {
            $rows = supabaseSelect('midwives', '*', ['email' => $current_email]);
            if ($rows && count($rows) > 0) {
                $target_data = $rows[0];
                $target_role = 'midwife';
            }
        }
        
        // Try by phone if not found by email
        if (!$target_data && !empty($current_phone)) {
            $rows = supabaseSelect('bhw', '*', ['phone_number' => $current_phone]);
            if ($rows && count($rows) > 0) {
                $target_data = $rows[0];
                $target_role = 'bhw';
            } else {
                $rows = supabaseSelect('midwives', '*', ['phone_number' => $current_phone]);
                if ($rows && count($rows) > 0) {
                    $target_data = $rows[0];
                    $target_role = 'midwife';
                }
            }
        }
    } else {
        // Get data for the target role
        if ($target_role === 'bhw') {
            if (!empty($current_email)) {
                $rows = supabaseSelect('bhw', '*', ['email' => $current_email]);
            } else {
                $rows = supabaseSelect('bhw', '*', ['phone_number' => $current_phone]);
            }
            if ($rows && count($rows) > 0) {
                $target_data = $rows[0];
            }
        } elseif ($target_role === 'midwife') {
            if (!empty($current_email)) {
                $rows = supabaseSelect('midwives', '*', ['email' => $current_email]);
            } else {
                $rows = supabaseSelect('midwives', '*', ['phone_number' => $current_phone]);
            }
            if ($rows && count($rows) > 0) {
                $target_data = $rows[0];
            }
        }
    }
    
    if (!$target_data || !$target_role) {
        echo json_encode([
            'status' => 'error',
            'message' => 'BHW or Midwife account not found. Cannot switch back to BHW/Midwife role.'
        ]);
        exit();
    }
    
    // Switch session from User to BHW/Midwife
    session_unset();
    
    if ($target_role === 'bhw') {
        $_SESSION['bhw_id'] = $target_data['bhw_id'];
        $_SESSION['fname'] = $target_data['fname'];
        $_SESSION['lname'] = $target_data['lname'];
        $_SESSION['email'] = $target_data['email'];
        $_SESSION['phone_number'] = $target_data['phone_number'] ?? null;
        $_SESSION['permissions'] = $target_data['permissions'] ?? null;
        $_SESSION['role'] = $target_data['role'] ?? 'bhw';
        $_SESSION['user_type'] = 'bhw';
        $_SESSION['logged_in'] = true;
    } elseif ($target_role === 'midwife') {
        $_SESSION['midwife_id'] = $target_data['midwife_id'];
        $_SESSION['fname'] = $target_data['fname'];
        $_SESSION['lname'] = $target_data['lname'];
        $_SESSION['email'] = $target_data['email'];
        $_SESSION['phone_number'] = $target_data['phone_number'] ?? null;
        $_SESSION['permissions'] = $target_data['permissions'] ?? null;
        $_SESSION['approve'] = 1;
        $_SESSION['role'] = $target_data['role'] ?? 'midwife';
        $_SESSION['profileimg'] = $target_data['profileimg'] ?? $target_data['profileImg'] ?? null;
        $_SESSION['user_type'] = 'midwife';
        $_SESSION['logged_in'] = true;
    }
    
    // Store available roles (user might have multiple roles)
    $available_roles = [];
    $available_roles[] = $target_role;
    
    // Check if they also have User role
    $user_check = supabaseSelect('users', '*', ['email' => $current_email]);
    if ($user_check && count($user_check) > 0) {
        $available_roles[] = 'user';
    }
    
    if (count($available_roles) > 1) {
        $_SESSION['available_roles'] = $available_roles;
    }
    
    // Log activity
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $actor_id = $target_role === 'bhw' ? $target_data['bhw_id'] : $target_data['midwife_id'];
    supabaseLogActivity($actor_id, $target_role, 'role_switch', 'User switched from Parent to ' . ucfirst($target_role) . ' role', $ip);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Successfully switched to ' . ucfirst($target_role) . ' role',
        'redirect_url' => '../../views/bhw-page/dashboard.php'
    ]);
    
} catch (Exception $e) {
    error_log("Switch back to BHW error: " . $e->getMessage());
    error_log("Switch back to BHW trace: " . $e->getTraceAsString());
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

