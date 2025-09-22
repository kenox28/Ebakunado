<?php
session_start();
include '../../../database/Database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($connect->connect_error) {
    die('Connection Failed: ' . $connect->connect_error);
}

header('Content-Type: application/json');

$baby_id = $_POST['baby_id'] ?? '';

// 1) Mark CHR accepted
$stmt = $connect->prepare("UPDATE Child_Health_Records SET status = 'accepted' WHERE baby_id = ?");
$stmt->bind_param('s', $baby_id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    echo json_encode(['status' => 'error', 'message' => 'Record not accepted']);
    $connect->close();
    exit();
}

// 2) If schedule already exists, skip (prevents duplicates)
$exists = 0;
$chk = $connect->prepare("SELECT COUNT(*) AS c FROM immunization_records WHERE baby_id = ?");
$chk->bind_param('s', $baby_id);
$chk->execute();
$chk->bind_result($exists);
$chk->fetch();
$chk->close();

if ($exists > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Record accepted. Schedule already exists.']);
    $connect->close();
    exit();
}

// 3) Get child's birth date for schedule baseline
$birth_date = '';
$info = $connect->prepare("SELECT child_birth_date FROM Child_Health_Records WHERE baby_id = ? LIMIT 1");
$info->bind_param('s', $baby_id);
$info->execute();
$info->bind_result($birth_date);
$info->fetch();
$info->close();

if (empty($birth_date)) {
    echo json_encode(['status' => 'success', 'message' => 'Record accepted. Missing birth date for schedule.']);
    $connect->close();
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

// 6) Insert schedule rows into new schema
$ins = $connect->prepare("INSERT INTO immunization_records (baby_id, vaccine_name, dose_number, status, catch_up_date) VALUES (?, ?, ?, 'scheduled', ?)");
$doseNum = 1;
foreach ($vaccines as $v) {
    $vname = $v[0];
    $sched = $v[1];
    $due = compute_due_date($birth_date, $sched);
    $ins->bind_param('ssis', $baby_id, $vname, $doseNum, $due);
    $ins->execute();
    $doseNum++;
}
$ins->close();

echo json_encode(['status' => 'success', 'message' => 'Record accepted and schedule created']);

$connect->close();
?>