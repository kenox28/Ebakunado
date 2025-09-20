<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';
header('Content-Type: application/json');

$baby_id = $_POST['baby_id'] ?? '';
if ($baby_id === '') { echo json_encode(['status'=>'error','message'=>'Missing baby_id']); exit(); }

// 1) Mark CHR accepted
$ok = supabaseUpdate('child_health_records', ['status' => 'accepted'], ['baby_id' => $baby_id]);
if ($ok === false) { echo json_encode(['status' => 'error', 'message' => 'Record not accepted']); exit(); }

// 2) If schedule already exists, skip
$exists = supabaseSelect('immunization_records', 'id', ['baby_id' => $baby_id], null, 1);
if ($exists && count($exists) > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Record accepted. Schedule already exists.']);
    exit();
}

// 3) Get child's birth date for schedule baseline
$child = supabaseSelect('child_health_records', 'child_birth_date', ['baby_id' => $baby_id], null, 1);
$birth_date = $child && isset($child[0]['child_birth_date']) ? $child[0]['child_birth_date'] : null;
if (empty($birth_date)) {
    echo json_encode(['status' => 'success', 'message' => 'Record accepted. Missing birth date for schedule.']);
    exit();
}

// 4) Vaccine list per RA 10152 (common infant schedule)
$vaccines = [
    ['BCG', 'at birth'],
    ['Hepatitis B (Birth dose)', 'at birth'],
    ['Pentavalent (DPT-HepB-Hib) - 1st', '6 weeks'],
    ['OPV - 1st', '6 weeks'],
    ['PCV - 1st', '6 weeks'],
    ['Pentavalent (DPT-HepB-Hib) - 2nd', '10 weeks'],
    ['OPV - 2nd', '10 weeks'],
    ['PCV - 2nd', '10 weeks'],
    ['Pentavalent (DPT-HepB-Hib) - 3rd', '14 weeks'],
    ['OPV - 3rd', '14 weeks'],
    ['PCV - 3rd', '14 weeks'],
    ['IPV', '14 weeks'],
    ['MMR / Measles - 1st', '9 months'],
    ['MMR / Measles - 2nd', '12 months']
];

// 5) Helper to compute due date (catch_up_date)
function compute_due_date($birth, $sched) {
    $d = new DateTime($birth);
    $s = strtolower($sched);
    if (strpos($s, 'birth') !== false) return $d->format('Y-m-d');
    if (preg_match('/(\d+)\s*weeks?/', $s, $m)) { $d->modify('+' . intval($m[1]) . ' week'); return $d->format('Y-m-d'); }
    if (preg_match('/(\d+)\s*months?/', $s, $m)) { $d->modify('+' . intval($m[1]) . ' month'); return $d->format('Y-m-d'); }
    return $d->format('Y-m-d');
}

// 6) Insert schedule rows
$doseNum = 1;
foreach ($vaccines as $v) {
    $vname = $v[0];
    $sched = $v[1];
    $due = compute_due_date($birth_date, $sched);
    supabaseInsert('immunization_records', [
        'baby_id' => $baby_id,
        'vaccine_name' => $vname,
        'dose_number' => $doseNum,
        'status' => 'scheduled',
        'catch_up_date' => $due
    ]);
    $doseNum++;
}

echo json_encode(['status' => 'success', 'message' => 'Record accepted and schedule created']);
?>


