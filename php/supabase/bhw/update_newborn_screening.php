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
if (empty($baby_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Baby ID is required']);
    exit();
}

// Get Newborn Screening data from POST
$nbs_data = [
    'date_newbornscreening' => $_POST['date_newbornScreening'] ?? null,
    'placenewbornscreening' => $_POST['placeNewbornScreening'] ?? '',
    'date_updated' => date('Y-m-d H:i:s')
];

// Convert empty string to null for date field
if (empty($nbs_data['date_newbornscreening'])) {
    $nbs_data['date_newbornscreening'] = null;
}

// Trim place field
$nbs_data['placenewbornscreening'] = trim($nbs_data['placenewbornscreening']);

try {
    $result = supabaseUpdate('child_health_records', $nbs_data, ['baby_id' => $baby_id]);
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Newborn Screening updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update Newborn Screening']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

