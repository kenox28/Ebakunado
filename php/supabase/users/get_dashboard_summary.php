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

try {
    // Get all accepted children for this user
    $children = supabaseSelect(
        'child_health_records', 
        'baby_id', 
        ['user_id' => $user_id, 'status' => 'accepted']
    );

    $totalChildren = $children ? count($children) : 0;
    $baby_ids = $children ? array_column($children, 'baby_id') : [];

    // Get approved CHR documents count
    $approvedCount = 0;
    $pendingCount = 0;
    $todayScheduleCount = 0;

    if (!empty($baby_ids)) {
        // Count approved CHR documents
        $approvedRequests = supabaseSelect(
            'chrdocrequest',
            'baby_id',
            ['user_id' => $user_id, 'status' => 'approved']
        );
        $approvedCount = $approvedRequests ? count($approvedRequests) : 0;

        // Count pending CHR documents
        $pendingRequests = supabaseSelect(
            'chrdocrequest',
            'baby_id',
            ['user_id' => $user_id, 'status' => 'pendingCHR']
        );
        $pendingCount = $pendingRequests ? count($pendingRequests) : 0;

        // Count children with schedules for today (guideline or batch date)
        $today = date('Y-m-d');
        $todayScheduleCount = 0;
        $todayBabies = [];

        $scheduledRecords = supabaseSelect(
            'immunization_records',
            'baby_id,schedule_date,batch_schedule_date,status',
            ['baby_id' => $baby_ids, 'status' => 'scheduled']
        );

        if ($scheduledRecords && is_array($scheduledRecords)) {
            foreach ($scheduledRecords as $record) {
                $targetDate = $record['batch_schedule_date'] ?? $record['schedule_date'] ?? null;
                if ($targetDate === $today) {
                    $bid = $record['baby_id'];
                    if ($bid && !isset($todayBabies[$bid])) {
                        $todayBabies[$bid] = true;
                    }
                }
            }
        }

        $todayScheduleCount = count($todayBabies);
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_children' => $totalChildren,
            'approved_chr_documents' => $approvedCount,
            'pending_chr_requests' => $pendingCount,
            'upcoming_schedule_today' => $todayScheduleCount
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
