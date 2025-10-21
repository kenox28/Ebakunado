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

// Get TD status data from POST
$td_data = [
    'mother_td_dose1_date' => $_POST['mother_td_dose1_date'] ?? null,
    'mother_td_dose2_date' => $_POST['mother_td_dose2_date'] ?? null,
    'mother_td_dose3_date' => $_POST['mother_td_dose3_date'] ?? null,
    'mother_td_dose4_date' => $_POST['mother_td_dose4_date'] ?? null,
    'mother_td_dose5_date' => $_POST['mother_td_dose5_date'] ?? null
];

// Convert empty strings to null for date fields
foreach (['mother_td_dose1_date', 'mother_td_dose2_date', 'mother_td_dose3_date', 'mother_td_dose4_date', 'mother_td_dose5_date'] as $field) {
    if (empty($td_data[$field])) {
        $td_data[$field] = null;
    }
}

try {
    // Resolve string users.user_id from child
    $child = supabaseSelect('child_health_records', 'user_id', ['baby_id' => $baby_id], null, 1);
    if (!$child || count($child) === 0) { echo json_encode(['status'=>'error','message'=>'Child not found']); exit(); }
    $string_user_id = $child[0]['user_id'] ?? '';

    // Existing row?
    $existing = supabaseSelect('mother_tetanus_doses', 'id', ['user_id' => $string_user_id], null, 1);
    $upd = [ 'date_updated' => date('Y-m-d H:i:s') ];
    if ($td_data['mother_td_dose1_date'] !== null) $upd['dose1_date'] = $td_data['mother_td_dose1_date'] ?: null;
    if ($td_data['mother_td_dose2_date'] !== null) $upd['dose2_date'] = $td_data['mother_td_dose2_date'] ?: null;
    if ($td_data['mother_td_dose3_date'] !== null) $upd['dose3_date'] = $td_data['mother_td_dose3_date'] ?: null;
    if ($td_data['mother_td_dose4_date'] !== null) $upd['dose4_date'] = $td_data['mother_td_dose4_date'] ?: null;
    if ($td_data['mother_td_dose5_date'] !== null) $upd['dose5_date'] = $td_data['mother_td_dose5_date'] ?: null;

    if ($existing && count($existing) > 0) {
        $ok = supabaseUpdate('mother_tetanus_doses', $upd, ['id' => $existing[0]['id']]);
        echo json_encode($ok ? ['status'=>'success'] : ['status'=>'error','message'=>'Failed to update TD status']);
    } else {
        $ins = array_merge([ 'user_id' => $string_user_id, 'date_created' => date('Y-m-d H:i:s') ], $upd);
        $ok = supabaseInsert('mother_tetanus_doses', $ins);
        echo json_encode($ok ? ['status'=>'success'] : ['status'=>'error','message'=>'Failed to insert TD status']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
