<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$baby_id = $_POST['baby_id'] ?? '';
if (empty($baby_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Baby ID is required']);
    exit();
}

// Get TD status data from POST
$td_data = [
    'mother_td_dose1_date' => $_POST['mother_td_dose1_date'] ?? null,
    'mother_td_dose2_date' => $_POST['mother_td_dose2_date'] ?? null,
    'mother_td_dose3_date' => $_POST['mother_td_dose3_date'] ?? null,
    'mother_td_dose4_date' => $_POST['mother_td_dose4_date'] ?? null,
    'mother_td_dose5_date' => $_POST['mother_td_dose5_date'] ?? null,
    'date_updated' => date('Y-m-d H:i:s')
];

// Convert empty strings to null for date fields
foreach (['mother_td_dose1_date', 'mother_td_dose2_date', 'mother_td_dose3_date', 'mother_td_dose4_date', 'mother_td_dose5_date'] as $field) {
    if (empty($td_data[$field])) {
        $td_data[$field] = null;
    }
}

try {
    $result = supabaseUpdate('child_health_records', $td_data, ['baby_id' => $baby_id]);
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'TD status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update TD status']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
