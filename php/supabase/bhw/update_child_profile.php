<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Only allow midwives to update child profile
if (!isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only midwives can update child profile.']);
    exit();
}

$baby_id = $_POST['baby_id'] ?? '';
if (empty($baby_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Baby ID is required']);
    exit();
}

// Get user_id from child record first
$child = supabaseSelect('child_health_records', 'user_id', ['baby_id' => $baby_id], null, 1);
if (!$child || count($child) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Child record not found']);
    exit();
}
$user_id = $child[0]['user_id'] ?? '';

// Get child profile data from POST (for child_health_records table)
$profile_data = [
    'place_of_birth' => $_POST['place_of_birth'] ?? '',
    'birth_weight' => !empty($_POST['birth_weight']) ? $_POST['birth_weight'] : null,
    'birth_height' => !empty($_POST['birth_height']) ? $_POST['birth_height'] : null,
    'address' => $_POST['address'] ?? '',
    'allergies' => $_POST['allergies'] ?? '',
    'blood_type' => $_POST['blood_type'] ?? '',
    'non_nhts' => $_POST['non_nhts'] ?? '',
    'father_name' => $_POST['father_name'] ?? '',
    'mother_name' => $_POST['mother_name'] ?? '',
    'placenewbornscreening' => $_POST['nb_screening'] ?? '',
    'family_planning' => $_POST['family_planning'] ?? '',
    'date_updated' => date('Y-m-d H:i:s')
];

// Get user profile data from POST (for users table)
$user_data = [
    'family_number' => $_POST['family_number'] ?? '',
    'philhealth_no' => $_POST['philhealth_no'] ?? '',
    'nhts' => $_POST['nhts'] ?? ''
];

// Remove empty values to avoid overwriting with empty strings
$profile_data = array_filter($profile_data, function($value) {
    return $value !== '';
});

$user_data = array_filter($user_data, function($value) {
    return $value !== '';
});

try {
    // Update child_health_records table
    $result1 = true;
    if (!empty($profile_data)) {
        $result1 = supabaseUpdate('child_health_records', $profile_data, ['baby_id' => $baby_id]);
    }
    
    // Update users table if user_id exists and user_data is not empty
    $result2 = true;
    if (!empty($user_id) && !empty($user_data)) {
        $result2 = supabaseUpdate('users', $user_data, ['user_id' => $user_id]);
    }
    
    if ($result1 && $result2) {
        echo json_encode(['status' => 'success', 'message' => 'Child profile updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update child profile']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

