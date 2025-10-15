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
    require_once __DIR__ . '/../../../assets/config/cloudinary.php';
    
    $cloudinary = \Cloudinary\Uploader::upload($file['tmp_name'], [
        'public_id' => 'profile_photos/' . pathinfo($filename, PATHINFO_FILENAME),
        'folder' => 'ebakunado/profiles',
        'resource_type' => 'image'
    ]);
    
    $imageUrl = $cloudinary['secure_url'];
    
    // Update user profile image in database
    $supabase = getSupabase();
    
    if ($user_type === 'midwife') {
        $response = $supabase->from('midwives')
            ->update(['profileImg' => $imageUrl, 'updated' => date('c')])
            ->eq('midwife_id', $user_id);
    } else {
        $response = $supabase->from('bhw')
            ->update(['profileImg' => $imageUrl, 'updated' => date('c')])
            ->eq('bhw_id', $user_id);
    }
    
    if ($response->getData()) {
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
    error_log("Profile photo upload error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload profile photo']);
}
?>
