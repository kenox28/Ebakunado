<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    $user_id = $_SESSION['user_id'];
    $filter = $_GET['filter'] ?? null; // 'upcoming' | 'missed' | null

    // Fetch children for this user
    $child_columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,status';
    $children = supabaseSelect('child_health_records', $child_columns, ['user_id' => $user_id]);

    $summary = [
        'upcoming_count' => 0,
        'missed_count' => 0,
        'items' => []
    ];

    if (!$children || count($children) === 0) {
        echo json_encode(['status'=>'success','data'=>$summary]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    $imm_columns = 'baby_id,vaccine_name,dose_number,status,schedule_date,catch_up_date,date_given';
    $immunizations = supabaseSelect('immunization_records', $imm_columns, ['baby_id' => $baby_ids]);

    // Group immunizations by baby
    $imm_by_baby = [];
    if ($immunizations) {
        foreach ($immunizations as $imm) {
            $imm_by_baby[$imm['baby_id']][] = $imm;
        }
    }

    $today = date('Y-m-d');

    foreach ($children as $child) {
        if ($child['status'] !== 'accepted') { continue; }
        $baby_id = $child['baby_id'];
        $imm_list = $imm_by_baby[$baby_id] ?? [];

        $missed = 0;
        $upcoming_date = null;
        $upcoming_name = null;

        foreach ($imm_list as $imm) {
            $status = $imm['status'];
            $sched = $imm['schedule_date'] ?: $imm['catch_up_date'];

            // missed: explicit missed OR scheduled before today but not taken
            if ($status === 'missed' || ($status === 'scheduled' && $sched && $sched < $today)) {
                $missed++;
            }

            // upcoming: scheduled today or future
            if ($status === 'scheduled' && $sched && $sched >= $today) {
                if ($upcoming_date === null || $sched < $upcoming_date) {
                    $upcoming_date = $sched;
                    $upcoming_name = $imm['vaccine_name'];
                }
            }
        }

        if ($upcoming_date) { $summary['upcoming_count']++; }
        if ($missed > 0) { $summary['missed_count']++; }

        if ($filter) {
            $include = false;
            if ($filter === 'upcoming' && $upcoming_date) { $include = true; }
            if ($filter === 'missed' && $missed > 0) { $include = true; }

            if ($include) {
                $summary['items'][] = [
                    'baby_id' => $baby_id,
                    'name' => trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? '')),
                    'upcoming_date' => $upcoming_date,
                    'upcoming_vaccine' => $upcoming_name,
                    'missed_count' => $missed
                ];
            }
        }
    }

    echo json_encode(['status'=>'success','data'=>$summary]);

} catch (Exception $e) {
    error_log('get_children_summary error: ' . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Failed to load summary']);
}
?>


