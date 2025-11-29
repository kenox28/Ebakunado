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
    $philhealth_no = $_POST['philhealth_no'] ?? '';
    $nhts = $_POST['nhts'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Prepare update data - only include fields that are provided (not empty)
    // This prevents empty strings from overwriting existing values
    $updateData = [
        'updated' => date('c')
    ];
    
    // Only add fields to update if they are provided and not empty
    if (!empty($fname)) {
        $updateData['fname'] = $fname;
    }
    if (!empty($lname)) {
        $updateData['lname'] = $lname;
    }
    if (!empty($email)) {
        $updateData['email'] = $email;
    }
    if (!empty($phone_number)) {
        $updateData['phone_number'] = $phone_number;
    }
    if (!empty($gender)) {
        $updateData['gender'] = $gender;
    }
    if (!empty($place)) {
        $updateData['place'] = $place;
    }
    // PhilHealth and NHTS can be explicitly set to empty/null, so check if they were provided
    if (isset($_POST['philhealth_no'])) {
        $updateData['philhealth_no'] = $philhealth_no !== '' ? $philhealth_no : null;
    }
    if (isset($_POST['nhts'])) {
        $updateData['nhts'] = $nhts !== '' ? $nhts : null;
    }
    
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
    
    // Fallback: if columns philhealth_no/nhts don't exist, retry without them
    if ($result === false) {
        $lastErr = isset($supabase) && method_exists($supabase, 'getLastError') ? (string)$supabase->getLastError() : '';
        $mayBeSchemaIssue = stripos($lastErr, 'philhealth_no') !== false || stripos($lastErr, 'nhts') !== false || stripos($lastErr, 'column') !== false;
        if ($mayBeSchemaIssue) {
            unset($updateData['philhealth_no'], $updateData['nhts']);
            $result = supabaseUpdate('users', $updateData, ['user_id' => $user_id]);
        }
    }
    
    if ($result !== false) {
        // Update session data - only update fields that were actually changed
        if (isset($updateData['fname'])) {
            $_SESSION['fname'] = $updateData['fname'];
        }
        if (isset($updateData['lname'])) {
            $_SESSION['lname'] = $updateData['lname'];
        }
        if (isset($updateData['email'])) {
            $_SESSION['email'] = $updateData['email'];
        }
        if (isset($updateData['phone_number'])) {
            $_SESSION['phone_number'] = $updateData['phone_number'];
        }
        if (isset($updateData['philhealth_no'])) {
            $_SESSION['philhealth_no'] = $philhealth_no;
        }
        if (isset($updateData['nhts'])) {
            $_SESSION['nhts'] = $nhts;
        }
        
        // Determine success message based on what was updated
        $updateMessage = 'Profile updated successfully';
        if (!empty($current_password) && !empty($new_password)) {
            $updateMessage = 'Password changed successfully';
        } elseif (!empty($fname) || !empty($lname)) {
            $updateMessage = 'Name updated successfully';
        } elseif (!empty($email)) {
            $updateMessage = 'Email updated successfully';
        } elseif (!empty($phone_number)) {
            $updateMessage = 'Phone number updated successfully';
        } elseif (!empty($gender)) {
            $updateMessage = 'Gender updated successfully';
        } elseif (!empty($place)) {
            $updateMessage = 'Place updated successfully';
        } elseif (isset($_POST['philhealth_no'])) {
            $updateMessage = 'PhilHealth number updated successfully';
        } elseif (isset($_POST['nhts'])) {
            $updateMessage = 'NHTS updated successfully';
        }
        
        echo json_encode(['status' => 'success', 'message' => $updateMessage]);
    } else {
        $err = isset($supabase) && method_exists($supabase, 'getLastError') ? $supabase->getLastError() : null;
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile', 'debug' => $err]);
    }
    
} catch (Exception $e) {
    error_log("User profile update error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}
?>
