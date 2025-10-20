<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$baby_id = $_POST['baby_id'] ?? '';
if ($baby_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing baby_id']);
    exit();
}

// Get all the form data
$updateData = [
    'child_fname' => $_POST['child_fname'] ?? '',
    'child_lname' => $_POST['child_lname'] ?? '',
    'child_gender' => $_POST['child_gender'] ?? '',
    'child_birth_date' => $_POST['child_birth_date'] ?? '',
    'place_of_birth' => $_POST['place_of_birth'] ?? '',
    'mother_name' => $_POST['mother_name'] ?? '',
    'father_name' => $_POST['father_name'] ?? '',
    'address' => $_POST['address'] ?? '',
    'birth_weight' => $_POST['birth_weight'] ? (float)$_POST['birth_weight'] : null,
    'birth_height' => $_POST['birth_height'] ? (float)$_POST['birth_height'] : null,
    'birth_attendant' => $_POST['birth_attendant'] ?? '',
    'delivery_type' => $_POST['delivery_type'] ?? '',
    'birth_order' => $_POST['birth_order'] ?? '',
    'blood_type' => isset($_POST['blood_type']) && $_POST['blood_type'] !== '' ? $_POST['blood_type'] : null,
    'allergies' => isset($_POST['allergies']) && $_POST['allergies'] !== '' ? $_POST['allergies'] : null,
    'lpm' => isset($_POST['lpm']) && $_POST['lpm'] !== '' ? $_POST['lpm'] : null,
    'family_planning' => isset($_POST['family_planning']) && $_POST['family_planning'] !== '' ? $_POST['family_planning'] : null,
    'date_updated' => date('Y-m-d H:i:s')
];

// Remove empty values to avoid overwriting with empty strings
$updateData = array_filter($updateData, function($value) {
    return $value !== '' && $value !== null;
});

try {
    $result = supabaseUpdate('child_health_records', $updateData, ['baby_id' => $baby_id]);
    
    if ($result !== false) {
        echo json_encode(['status' => 'success', 'message' => 'Child information updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update child information']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
