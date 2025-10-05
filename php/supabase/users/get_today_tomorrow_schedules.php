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
        'baby_id,child_fname,child_lname', 
        ['user_id' => $user_id, 'status' => 'accepted']
    );

    if (!$children) {
        echo json_encode([
            'status' => 'success', 
            'data' => ['today' => [], 'tomorrow' => []]
        ]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    $today_schedules = [];
    $tomorrow_schedules = [];

    // Get today's and tomorrow's dates
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    foreach ($baby_ids as $baby_id) {
        $vaccinations = supabaseSelect(
            'immunization_records',
            'id,vaccine_name,dose_number,schedule_date,status',
            ['baby_id' => $baby_id]
        );

        if ($vaccinations) {
            foreach ($vaccinations as $vaccination) {
                // Only include scheduled/upcoming vaccines
                if (in_array($vaccination['status'], ['scheduled', 'pending']) && !empty($vaccination['schedule_date'])) {
                    // Find child name
                    $child_name = '';
                    foreach ($children as $child) {
                        if ($child['baby_id'] === $baby_id) {
                            $child_name = $child['child_fname'] . ' ' . $child['child_lname'];
                            break;
                        }
                    }

                    $schedule_data = [
                        'id' => $vaccination['id'],
                        'baby_id' => $baby_id,
                        'child_name' => $child_name,
                        'vaccine_name' => $vaccination['vaccine_name'],
                        'dose_number' => $vaccination['dose_number'],
                        'schedule_date' => $vaccination['schedule_date'],
                        'status' => $vaccination['status']
                    ];

                    if ($vaccination['schedule_date'] === $today) {
                        $today_schedules[] = $schedule_data;
                    } elseif ($vaccination['schedule_date'] === $tomorrow) {
                        $tomorrow_schedules[] = $schedule_data;
                    }
                }
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'today' => $today_schedules,
            'tomorrow' => $tomorrow_schedules
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
