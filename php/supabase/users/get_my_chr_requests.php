<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Return approved requests for this user, latest first
$rows = supabaseSelect('chrdocrequest', '*', ['user_id' => $user_id, 'status' => 'approved'], 'approved_at.desc');
$rows = $rows ?: [];

// Enrich with child name from child_health_records
$enriched = [];
foreach ($rows as $r) {
    $babyId = $r['baby_id'] ?? '';
    $child = null;
    if ($babyId !== '') {
        $c = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $babyId], null, 1);
        if ($c && count($c) > 0) { $child = $c[0]; }
    }
    $r['child_name'] = trim(($child['child_fname'] ?? '').' '.($child['child_lname'] ?? ''));
    $enriched[] = $r;
}

echo json_encode(['status' => 'success', 'data' => $enriched]);
exit();
?>


