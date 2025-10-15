<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

$family_code = $_POST['family_code'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($family_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Family code is required']);
    exit();
}

// Find record with family code
$record = supabaseSelect('child_health_records', '*', ['user_id' => $family_code]);

if (!$record) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid family code']);
    exit();
}

// Update record with actual user_id
$update = supabaseUpdate('child_health_records', 
    ['user_id' => $user_id, 'date_updated' => date('Y-m-d H:i:s')], 
    ['user_id' => $family_code]
);

if ($update !== false) {
    // Create immunization records for the child
    createImmunizationRecords($record['baby_id'], $record['child_birth_date']);
    
        echo json_encode([
            'status' => 'success',
            'message' => 'Child added successfully',
            'child_name' => $record['child_fname'] . ' ' . $record['child_lname'],
            'baby_id' => $record['baby_id']
        ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add child']);
}

function createImmunizationRecords($baby_id, $birth_date) {
    $vaccines = [
        ['BCG', 'at birth'],
        ['HEPAB1 (w/in 24 hrs)', 'at birth'],
        ['HEPAB1 (More than 24hrs)', 'at birth'],
        ['Pentavalent (DPT-HepB-Hib) - 1st', '6 weeks'],
        ['OPV - 1st', '6 weeks'],
        ['PCV - 1st', '6 weeks'],
        ['Rota Virus Vaccine - 1st', '6 weeks'],
        ['Pentavalent (DPT-HepB-Hib) - 2nd', '10 weeks'],
        ['OPV - 2nd', '10 weeks'],
        ['PCV - 2nd', '10 weeks'],
        ['Rota Virus Vaccine - 2nd', '10 weeks'],
        ['Pentavalent (DPT-HepB-Hib) - 3rd', '14 weeks'],
        ['OPV - 3rd', '14 weeks'],
        ['PCV - 3rd', '14 weeks'],
        ['MCV1 (AMV)', '9 months'],
        ['MCV2 (MMR)', '12 months']
    ];
    
    $doseNum = 1;
    foreach ($vaccines as $vaccine) {
        $vname = $vaccine[0];
        $sched = $vaccine[1];
        $due = compute_due_date($birth_date, $sched);
        
        supabaseInsert('immunization_records', [
            'baby_id' => $baby_id,
            'vaccine_name' => $vname,
            'dose_number' => $doseNum,
            'status' => 'scheduled',
            'schedule_date' => $due,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $doseNum++;
    }
}

function compute_due_date($birth, $sched) {
    $d = new DateTime($birth);
    $s = strtolower($sched);
    if (strpos($s, 'birth') !== false) return $d->format('Y-m-d');
    if (preg_match('/(\d+)\s*weeks?/', $s, $m)) { $d->modify('+' . intval($m[1]) . ' week'); return $d->format('Y-m-d'); }
    if (preg_match('/(\d+)\s*months?/', $s, $m)) { $d->modify('+' . intval($m[1]) . ' month'); return $d->format('Y-m-d'); }
    return $d->format('Y-m-d');
}
?>
