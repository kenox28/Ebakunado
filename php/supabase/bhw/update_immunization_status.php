<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$record_id = $_POST['record_id'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($record_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Record ID is required']);
    exit();
}

try {
    $update_data = [
        'status' => $status,
        'updated' => date('Y-m-d H:i:s')
    ];
    
    $result = supabaseUpdate('immunization_records', $update_data, ['id' => $record_id]);
    
    if ($result) {
        // Log activity: BHW/Midwife updated immunization status
        try {
            // Get approver info
            $approver_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
            $approver_type = isset($_SESSION['midwife_id']) ? 'midwife' : 'bhw';
            $approver_name = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
            
            // Get immunization record details
            $immunization_record = supabaseSelect('immunization_records', 'baby_id,vaccine_name,dose_number', ['id' => $record_id], null, 1);
            $baby_id = '';
            $vaccine_name = 'Unknown Vaccine';
            $dose_number = 1;
            if ($immunization_record && count($immunization_record) > 0) {
                $baby_id = $immunization_record[0]['baby_id'] ?? '';
                $vaccine_name = $immunization_record[0]['vaccine_name'] ?? 'Unknown Vaccine';
                $dose_number = $immunization_record[0]['dose_number'] ?? 1;
            }
            
            // Get child info for logging
            $child_name = 'Unknown Child';
            $mother_name = 'Unknown Mother';
            if ($baby_id) {
                $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname,mother_name', ['baby_id' => $baby_id], null, 1);
                if ($child_info && count($child_info) > 0) {
                    $child_name = trim(($child_info[0]['child_fname'] ?? '') . ' ' . ($child_info[0]['child_lname'] ?? ''));
                    $mother_name = $child_info[0]['mother_name'] ?? 'Unknown Mother';
                }
            }
            
            supabaseLogActivity(
                $approver_id,
                $approver_type,
                'IMMUNIZATION_STATUS_UPDATED',
                $approver_name . ' updated ' . $vaccine_name . ' (Dose ' . $dose_number . ') status to ' . $status . ' for ' . $child_name . ', child of ' . $mother_name . ' (Baby ID: ' . $baby_id . ', Record ID: ' . $record_id . ')',
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (Exception $e) {
            // Log error but don't fail the update
            error_log('Failed to log immunization status update activity: ' . $e->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
