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
    $filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
    
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
    $result = supabaseUpdate('users', $updateData, ['user_id' => $user_id]);
    
    if ($result !== false) {
        // Update session
        $_SESSION['profileimg'] = $imageUrl;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Profile photo updated successfully',
            'imageUrl' => $imageUrl
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile photo in database']);
    }
    
} catch (Exception $e) {
    error_log("User profile photo upload error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload profile photo']);
}
?>
