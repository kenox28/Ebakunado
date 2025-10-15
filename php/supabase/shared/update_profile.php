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
    $supabase = getSupabase();
    
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
            $response = $supabase->from('midwives')
                ->select('pass, salt')
                ->eq('midwife_id', $user_id)
                ->single();
        } else {
            $response = $supabase->from('bhw')
                ->select('pass, salt')
                ->eq('bhw_id', $user_id)
                ->single();
        }
        
        if ($response->getData()) {
            $userData = $response->getData();
            $storedHash = $userData['pass'];
            $salt = $userData['salt'];
            
            // Verify current password
            $currentHash = hash('sha256', $current_password . $salt);
            if ($currentHash !== $storedHash) {
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
                exit();
            }
            
            // Hash new password
            $newSalt = bin2hex(random_bytes(32));
            $newHash = hash('sha256', $new_password . $newSalt);
            
            $updateData['pass'] = $newHash;
            $updateData['salt'] = $newSalt;
        }
    }
    
    // Update user data
    if ($user_type === 'midwife') {
        $response = $supabase->from('midwives')
            ->update($updateData)
            ->eq('midwife_id', $user_id);
    } else {
        $response = $supabase->from('bhw')
            ->update($updateData)
            ->eq('bhw_id', $user_id);
    }
    
    if ($response->getData()) {
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
