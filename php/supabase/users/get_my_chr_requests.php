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

if (empty($rows)) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit();
}

// Get all baby_ids from requests in one batch
$babyIds = array_filter(array_column($rows, 'baby_id'));

// Fetch ALL child names in ONE query instead of one per request (fixes N+1 problem)
$childrenMap = [];
if (!empty($babyIds)) {
    $children = supabaseSelect('child_health_records', 'baby_id,child_fname,child_lname', ['baby_id' => $babyIds]);
    if ($children) {
        foreach ($children as $child) {
            $bid = $child['baby_id'] ?? '';
            if ($bid !== '') {
                $childrenMap[$bid] = trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? ''));
            }
        }
    }
}

// Enrich requests with child names from the map
$enriched = [];
foreach ($rows as $r) {
    $babyId = $r['baby_id'] ?? '';
    $r['child_name'] = $childrenMap[$babyId] ?? '';
    $enriched[] = $r;
}

echo json_encode(['status' => 'success', 'data' => $enriched]);
exit();
?>


