<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // 1) Fetch all children for this user (ids + names + status)
    $children = supabaseSelect(
        'child_health_records',
        'baby_id,child_fname,child_lname,status',
        ['user_id' => $user_id],
        'date_created.desc'
    );

    if (!$children || count($children) === 0) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit();
    }

    $babyIds = array_column($children, 'baby_id');

    // Helper to map by baby_id
    $makeIndex = function(array $rows, string $key) {
        $m = [];
        foreach ($rows as $r) { $m[$r[$key]][] = $r; }
        return $m;
    };

    // 2) Fetch all CHR requests for those baby_ids in one query, newest first
    $requests = supabaseSelect(
        'chrdocrequest',
        '*',
        ['user_id' => $user_id, 'baby_id' => $babyIds],
        'created_at.desc'
    ) ?: [];
    $reqByBaby = [];
    foreach ($requests as $r) {
        $bid = $r['baby_id'] ?? '';
        if ($bid === '') continue;
        if (!isset($reqByBaby[$bid])) { $reqByBaby[$bid] = $r; }
    }

    // 3) Fetch latest taken immunization per baby in one query
    $taken = supabaseSelect(
        'immunization_records',
        'baby_id,date_given,status',
        ['baby_id' => $babyIds, 'status' => 'taken'],
        'baby_id.asc,date_given.desc'
    ) ?: [];
    $latestTakenByBaby = [];
    foreach ($taken as $t) {
        $bid = $t['baby_id'] ?? '';
        if ($bid === '') continue;
        if (!isset($latestTakenByBaby[$bid])) { $latestTakenByBaby[$bid] = $t; }
    }

    // 4) Build response rows per child
    $out = [];
    foreach ($children as $c) {
        $bid = $c['baby_id'];
        $latestReq = $reqByBaby[$bid] ?? null;
        $latestTaken = $latestTakenByBaby[$bid] ?? null;

        $hasNewer = false;
        if ($latestReq && ($latestReq['status'] ?? '') === 'approved' && !empty($latestReq['created_at']) && $latestTaken && !empty($latestTaken['date_given'])) {
            $vaccinationDateOnly = date('Y-m-d', strtotime($latestTaken['date_given']));
            $chrDateOnly = date('Y-m-d', strtotime($latestReq['created_at']));
            if ($vaccinationDateOnly >= $chrDateOnly) { $hasNewer = true; }
        }

        $chrStatus = 'none';
        if ($latestReq) {
            if (($latestReq['status'] ?? '') === 'pendingCHR') { $chrStatus = 'pending'; }
            elseif (($latestReq['status'] ?? '') === 'approved') { $chrStatus = $hasNewer ? 'new_records' : 'approved'; }
        }

        $out[] = [
            'baby_id' => $bid,
            'child_fname' => $c['child_fname'] ?? '',
            'child_lname' => $c['child_lname'] ?? '',
            'chr_status' => $chrStatus,
            'latest_request' => $latestReq,
            'has_newer_records' => $hasNewer,
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $out]);
    exit();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
}
?>


