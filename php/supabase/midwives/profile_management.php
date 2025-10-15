<?php
/**
 * Profile Management System for BHW and Midwives
 * Handles profile data retrieval and updates
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Get user type and ID
$user_type = null;
$user_id = null;
$table = null;
$id_field = null;

if (isset($_SESSION['bhw_id'])) {
    $user_type = 'bhw';
    $user_id = $_SESSION['bhw_id'];
    $table = 'bhw';
    $id_field = 'bhw_id';
} elseif (isset($_SESSION['midwife_id'])) {
    $user_type = 'midwife';
    $user_id = $_SESSION['midwife_id'];
    $table = 'midwives';
    $id_field = 'midwife_id';
} else {
    echo json_encode(['status' => 'error', 'message' => 'No valid session found']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get current profile data
    try {
        $profile = supabaseSelect($table, '*', [$id_field => $user_id], null, 1);
        
        if ($profile && count($profile) > 0) {
            // Remove sensitive data
            unset($profile[0]['pass']);
            unset($profile[0]['salt']);
            
            echo json_encode([
                'status' => 'success',
                'data' => $profile[0]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Profile not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile data
    try {
        $update_data = [
            'fname' => trim($_POST['fname'] ?? ''),
            'lname' => trim($_POST['lname'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'gender' => trim($_POST['gender'] ?? ''),
            'place' => trim($_POST['place'] ?? ''),
            'updated' => date('Y-m-d H:i:s')
        ];
        
        // Validate required fields
        if (empty($update_data['fname']) || empty($update_data['lname']) || empty($update_data['email'])) {
            echo json_encode(['status' => 'error', 'message' => 'First name, last name, and email are required']);
            exit();
        }
        
        // Validate email format
        if (!filter_var($update_data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            exit();
        }
        
        // Handle password update if provided
        if (!empty($_POST['new_password'])) {
            $new_password = trim($_POST['new_password']);
            
            // Validate password strength
            if (strlen($new_password) < 6) {
                echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters long']);
                exit();
            }
            
            $salt = bin2hex(random_bytes(32));
            $hashed_password = hash('sha256', $new_password . $salt);
            $update_data['pass'] = $hashed_password;
            $update_data['salt'] = $salt;
        }
        
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $upload_result = handleProfileImageUpload($_FILES['profile_image'], $user_id, $user_type);
            if ($upload_result['success']) {
                $update_data['profileImg'] = $upload_result['filename'];
            } else {
                echo json_encode(['status' => 'error', 'message' => $upload_result['message']]);
                exit();
            }
        }
        
        // Update profile in database
        $result = supabaseUpdate($table, $update_data, [$id_field => $user_id]);
        
        if ($result !== false) {
            // Update session data
            $_SESSION['fname'] = $update_data['fname'];
            $_SESSION['lname'] = $update_data['lname'];
            $_SESSION['email'] = $update_data['email'];
            $_SESSION['phone_number'] = $update_data['phone_number'];
            $_SESSION['gender'] = $update_data['gender'];
            $_SESSION['place'] = $update_data['place'];
            
            if (isset($update_data['profileImg'])) {
                $_SESSION['profileImg'] = $update_data['profileImg'];
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Profile updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update profile'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Update error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

/**
 * Handle profile image upload
 * @param array $file - $_FILES array element
 * @param string $user_id - User ID
 * @param string $user_type - User type (bhw/midwife)
 * @return array - Result array with success status and message/filename
 */
function handleProfileImageUpload($file, $user_id, $user_type) {
    try {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF images are allowed.'];
        }
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File size too large. Maximum size is 5MB.'];
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = '../../../assets/images/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $user_type . '_' . $user_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Upload error: ' . $e->getMessage()];
    }
}
?>
