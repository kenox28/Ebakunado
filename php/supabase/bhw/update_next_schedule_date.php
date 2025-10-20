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
$catch_up_date = $_POST['catch_up_date'] ?? '';

if (empty($record_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Record ID is required']);
    exit();
}

try {
    $update_data = [
        'catch_up_date' => $catch_up_date ?: null,
        'updated' => date('Y-m-d H:i:s')
    ];
    
    $result = supabaseUpdate('immunization_records', $update_data, ['id' => $record_id]);
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Next schedule date updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update next schedule date']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
