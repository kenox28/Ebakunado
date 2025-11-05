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
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'No photo uploaded']);
        exit();
    }
    
    $file = $_FILES['photo'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
        exit();
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['status' => 'error', 'message' => 'File too large. Maximum size is 5MB.']);
        exit();
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $user_type . '_' . $user_id . '_' . time() . '.' . $extension;
    
    // Upload to Cloudinary
    require_once __DIR__ . '/../../../vendor/autoload.php';
    $cloudinaryConfig = include __DIR__ . '/../../../assets/config/cloudinary.php';
    
    \Cloudinary\Configuration\Configuration::instance([
        'cloud' => [
            'cloud_name' => $cloudinaryConfig['cloud_name'],
            'api_key' => $cloudinaryConfig['api_key'],
            'api_secret' => $cloudinaryConfig['api_secret']
        ],
        'url' => ['secure' => $cloudinaryConfig['secure']]
    ]);
    
    $uploader = new \Cloudinary\Api\Upload\UploadApi();
    $cloudinary = $uploader->upload($file['tmp_name'], [
        'public_id' => 'profile_photos/' . pathinfo($filename, PATHINFO_FILENAME),
        'folder' => 'ebakunado/profiles',
        'resource_type' => 'image'
    ]);
    
    $imageUrl = $cloudinary['secure_url'] ?? ($cloudinary['url'] ?? null);
    
    // Update user profile image in database
    $updateData = ['profileimg' => $imageUrl, 'updated' => date('c')];
    
    if ($user_type === 'midwife') {
        $result = supabaseUpdate('midwives', $updateData, ['midwife_id' => $user_id]);
    } else {
        $result = supabaseUpdate('bhw', $updateData, ['bhw_id' => $user_id]);
    }
    
    if ($result !== false) {
        // Check if user also exists in users table and sync the profile photo
        $userExistsInUsersTable = false;
        $userUserId = null;
        
        // Get current user data to find matching user in users table
        if ($user_type === 'midwife') {
            $currentUserData = supabaseSelect('midwives', '*', ['midwife_id' => $user_id]);
        } else {
            $currentUserData = supabaseSelect('bhw', '*', ['bhw_id' => $user_id]);
        }
        
        // Check if user exists in users table (check directly, not just if available_roles has 'user')
        if ($currentUserData && !empty($currentUserData)) {
            // Handle different response formats
            $currentData = null;
            if (is_array($currentUserData)) {
                if (isset($currentUserData[0])) {
                    $currentData = is_array($currentUserData[0]) ? $currentUserData[0] : (array)$currentUserData[0];
                } else {
                    $currentData = $currentUserData;
                }
            } else {
                $currentData = (array)$currentUserData;
            }
            
            // Extract email and phone - try different possible column names
            $email = $currentData['email'] ?? $currentData['Email'] ?? '';
            $phone = $currentData['phone_number'] ?? $currentData['phoneNumber'] ?? $currentData['phone'] ?? '';
            
            error_log("Photo sync - Extracted email: $email, phone: $phone");
            
            // Try to find user by email first
            if (!empty($email)) {
                $emailCheck = supabaseSelect('users', 'user_id', ['email' => $email]);
                if ($emailCheck && !empty($emailCheck)) {
                    $emailResult = is_array($emailCheck[0]) ? $emailCheck[0] : (array)$emailCheck[0];
                    $userUserId = $emailResult['user_id'] ?? $emailResult['userId'] ?? null;
                    if ($userUserId) {
                        $userExistsInUsersTable = true;
                        error_log("Found user in users table by email for photo sync: $email, user_id: $userUserId");
                    }
                }
            }
            
            // If not found by email, try phone number
            if (!$userExistsInUsersTable && !empty($phone)) {
                $phoneCheck = supabaseSelect('users', 'user_id', ['phone_number' => $phone]);
                if ($phoneCheck && !empty($phoneCheck)) {
                    $phoneResult = is_array($phoneCheck[0]) ? $phoneCheck[0] : (array)$phoneCheck[0];
                    $userUserId = $phoneResult['user_id'] ?? $phoneResult['userId'] ?? null;
                    if ($userUserId) {
                        $userExistsInUsersTable = true;
                        error_log("Found user in users table by phone for photo sync: $phone, user_id: $userUserId");
                    }
                }
            }
            
            if (!$userExistsInUsersTable) {
                error_log("User not found in users table for photo sync. Email: $email, Phone: $phone");
            }
        } else {
            error_log("ERROR: Could not retrieve current user data for photo sync from " . $user_type . " table for ID: $user_id");
        }
        
        // If user exists in users table, also update profile photo there
        if ($userExistsInUsersTable && $userUserId) {
            $usersUpdateData = ['profileimg' => $imageUrl, 'updated' => date('c')];
            error_log("Attempting to sync profile photo to users table. user_id: $userUserId, imageUrl: $imageUrl");
            $usersUpdateResult = supabaseUpdate('users', $usersUpdateData, ['user_id' => $userUserId]);
            
            if ($usersUpdateResult === false) {
                error_log("ERROR: Failed to sync profile photo to users table for user_id: $userUserId");
            } else {
                error_log("SUCCESS: Profile photo synced to users table for user_id: $userUserId");
            }
        } else {
            error_log("Skipping users table photo sync - user not found. userExistsInUsersTable: " . ($userExistsInUsersTable ? 'true' : 'false') . ", userUserId: " . ($userUserId ?? 'null'));
        }
        
        // Update session
        $_SESSION['profileimg'] = $imageUrl;
        
        // Prepare response with debug info
        $response = [
            'status' => 'success',
            'message' => 'Profile photo updated successfully',
            'imageUrl' => $imageUrl,
            'debug' => [
                'user_exists_in_users_table' => $userExistsInUsersTable,
                'user_user_id' => $userUserId,
                'email_checked' => isset($email) ? $email : null,
                'phone_checked' => isset($phone) ? $phone : null,
                'current_user_data_found' => !empty($currentUserData),
                'sync_attempted' => $userExistsInUsersTable && $userUserId,
                'sync_success' => isset($usersUpdateResult) ? ($usersUpdateResult !== false) : false,
                'user_type' => $user_type,
                'user_id' => $user_id
            ]
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile photo in database']);
    }
    
} catch (Exception $e) {
    error_log("Profile photo upload error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload profile photo']);
}
?>
