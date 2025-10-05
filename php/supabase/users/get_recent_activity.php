<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $activities = [];

    // Get recent child health record status changes
    $child_records = supabaseSelect(
        'child_health_records',
        'child_fname,child_lname,status,date_created,date_updated',
        ['user_id' => $user_id],
        'date_updated.desc',
        10
    );

    if ($child_records) {
        foreach ($child_records as $record) {
            $activity_type = 'approval';
            $title = 'Request Approved';
            $description = "Child registration for {$record['child_fname']} {$record['child_lname']} has been approved";
            
            if ($record['status'] === 'rejected') {
                $activity_type = 'rejection';
                $title = 'Request Rejected';
                $description = "Child registration for {$record['child_fname']} {$record['child_lname']} has been rejected";
            } elseif ($record['status'] === 'pending') {
                $activity_type = 'schedule';
                $title = 'Request Submitted';
                $description = "Child registration for {$record['child_fname']} {$record['child_lname']} has been submitted";
            }

            $activities[] = [
                'type' => $activity_type,
                'title' => $title,
                'description' => $description,
                'timestamp' => $record['date_updated'] ?: $record['date_created']
            ];
        }
    }

    // Get recent vaccination completions
    $children = supabaseSelect(
        'child_health_records',
        'baby_id,child_fname,child_lname',
        ['user_id' => $user_id, 'status' => 'accepted']
    );

    if ($children) {
        foreach ($children as $child) {
            $vaccinations = supabaseSelect(
                'immunization_records',
                'vaccine_name,status,date_given',
                ['baby_id' => $child['baby_id']],
                'date_given.desc',
                5
            );

            if ($vaccinations) {
                foreach ($vaccinations as $vaccination) {
                    if ($vaccination['status'] === 'completed' || $vaccination['status'] === 'taken') {
                        $activities[] = [
                            'type' => 'vaccine',
                            'title' => 'Vaccine Completed',
                            'description' => "{$vaccination['vaccine_name']} completed for {$child['child_fname']} {$child['child_lname']}",
                            'timestamp' => $vaccination['date_given']
                        ];
                    }
                }
            }
        }
    }

    // Sort activities by timestamp (most recent first)
    usort($activities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // Limit to 10 most recent activities
    $activities = array_slice($activities, 0, 10);

    echo json_encode([
        'status' => 'success',
        'data' => $activities
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
