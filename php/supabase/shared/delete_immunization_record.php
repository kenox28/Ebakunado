<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$record_id = $_POST['record_id'] ?? '';

if (empty($record_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Record ID is required']);
    exit();
}

try {
    $result = supabaseDelete('immunization_records', ['id' => $record_id]);
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Immunization record deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete immunization record']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
