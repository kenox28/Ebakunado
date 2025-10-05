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
    // Get all accepted children for this user
    $children = supabaseSelect(
        'child_health_records', 
        'baby_id', 
        ['user_id' => $user_id, 'status' => 'accepted']
    );

    if (!$children) {
        echo json_encode([
            'status' => 'success', 
            'data' => ['upcoming' => 0, 'completed' => 0]
        ]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    $upcoming_count = 0;
    $completed_count = 0;

    // Get vaccination records for all children
    foreach ($baby_ids as $baby_id) {
        $vaccinations = supabaseSelect(
            'immunization_records',
            'status,date_given,schedule_date',
            ['baby_id' => $baby_id]
        );

        if ($vaccinations) {
            foreach ($vaccinations as $vaccination) {
                if ($vaccination['status'] === 'completed' || $vaccination['status'] === 'taken') {
                    $completed_count++;
                } elseif ($vaccination['status'] === 'scheduled' || $vaccination['status'] === 'pending') {
                    // Check if the schedule date is within the next 30 days
                    if (!empty($vaccination['schedule_date'])) {
                        $schedule_date = new DateTime($vaccination['schedule_date']);
                        $today = new DateTime();
                        $diff = $schedule_date->diff($today);
                        
                        if ($schedule_date >= $today && $diff->days <= 30) {
                            $upcoming_count++;
                        }
                    }
                }
            }
        }
    }

    echo json_encode([
        'status' => 'success', 
        'data' => [
            'upcoming' => $upcoming_count,
            'completed' => $completed_count
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
