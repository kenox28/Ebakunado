<?php

session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

$baby_id = $_POST['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

// Check authorization
if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit(); }

// Get approver info
$approver_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
$approver_type = isset($_SESSION['midwife_id']) ? 'midwife' : 'bhw';
$approver_name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');

// Get child info for logging BEFORE deletion
$child_info = supabaseSelect('child_health_records', 'child_fname,child_lname,user_id,mother_name', ['baby_id' => $baby_id], null, 1);
$child_name = 'Unknown Child';
$mother_name = 'Unknown Mother';
if ($child_info && count($child_info) > 0) {
    $child_name = trim(($child_info[0]['child_fname'] ?? '') . ' ' . ($child_info[0]['child_lname'] ?? ''));
    $mother_name = $child_info[0]['mother_name'] ?? 'Unknown Mother';
}

// Delete the record from database
$ok = supabaseDelete('child_health_records', ['baby_id' => $baby_id]);

if ($ok !== false) {
    // Log activity: BHW/Midwife rejected and removed child registration
    try {
        supabaseLogActivity(
            $approver_id,
            $approver_type,
            'CHILD_REGISTRATION_REJECTED',
            $approver_name . ' rejected and removed child registration for ' . $child_name . ', child of ' . $mother_name . ' (Baby ID: ' . $baby_id . ')',
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    } catch (Exception $e) {
        // Log error but don't fail the rejection
        error_log('Failed to log child registration rejection activity: ' . $e->getMessage());
    }
}

echo json_encode(['status' => $ok !== false ? 'success' : 'error', 'message' => $ok !== false ? 'Record rejected and removed' : 'Record not rejected']);

?>


