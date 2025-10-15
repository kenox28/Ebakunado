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

// Get feeding data from POST
$feeding_data = [
    'exclusive_breastfeeding_1mo' => isset($_POST['exclusive_breastfeeding_1mo']) ? (bool)$_POST['exclusive_breastfeeding_1mo'] : false,
    'exclusive_breastfeeding_2mo' => isset($_POST['exclusive_breastfeeding_2mo']) ? (bool)$_POST['exclusive_breastfeeding_2mo'] : false,
    'exclusive_breastfeeding_3mo' => isset($_POST['exclusive_breastfeeding_3mo']) ? (bool)$_POST['exclusive_breastfeeding_3mo'] : false,
    'exclusive_breastfeeding_4mo' => isset($_POST['exclusive_breastfeeding_4mo']) ? (bool)$_POST['exclusive_breastfeeding_4mo'] : false,
    'exclusive_breastfeeding_5mo' => isset($_POST['exclusive_breastfeeding_5mo']) ? (bool)$_POST['exclusive_breastfeeding_5mo'] : false,
    'exclusive_breastfeeding_6mo' => isset($_POST['exclusive_breastfeeding_6mo']) ? (bool)$_POST['exclusive_breastfeeding_6mo'] : false,
    'complementary_feeding_6mo' => $_POST['complementary_feeding_6mo'] ?? '',
    'complementary_feeding_7mo' => $_POST['complementary_feeding_7mo'] ?? '',
    'complementary_feeding_8mo' => $_POST['complementary_feeding_8mo'] ?? '',
    'date_updated' => date('Y-m-d H:i:s')
];

try {
    $result = supabaseUpdate('child_health_records', $feeding_data, ['baby_id' => $baby_id]);
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Feeding status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update feeding status']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
