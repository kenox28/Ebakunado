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
        'baby_id,child_fname,child_lname,child_birth_date', 
        ['user_id' => $user_id, 'status' => 'accepted']
    );

    if (!$children) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'totalChildren' => 0,
                'overdueVaccines' => 0,
                'upcomingVaccines' => 0,
                'completedToday' => 0,
                'children' => []
            ]
        ]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    $today = date('Y-m-d');
    $nextWeek = date('Y-m-d', strtotime('+7 days'));
    
    $totalChildren = count($children);
    $overdueVaccines = 0;
    $upcomingVaccines = 0;
    $completedToday = 0;
    $childrenData = [];

    foreach ($children as $child) {
        $baby_id = $child['baby_id'];
        
        // Get all immunization records for this child
        $vaccinations = supabaseSelect(
            'immunization_records',
            'vaccine_name,status,schedule_date,date_given,catch_up_date',
            ['baby_id' => $baby_id]
        );

        $childOverdue = 0;
        $childUpcoming = 0;
        $childCompletedToday = 0;
        $childProgress = 0;
        $totalVaccines = 0;
        $completedVaccines = 0;

        if ($vaccinations) {
            foreach ($vaccinations as $vaccination) {
                $totalVaccines++;
                
                // Count completed vaccines
                if (in_array($vaccination['status'], ['completed', 'taken'])) {
                    $completedVaccines++;
                    
                    // Check if completed today
                    if ($vaccination['date_given'] === $today) {
                        $childCompletedToday++;
                        $completedToday++;
                    }
                }
                
                // Check for overdue vaccines (scheduled date passed but not completed)
                if ($vaccination['status'] === 'scheduled' && 
                    $vaccination['schedule_date'] && 
                    $vaccination['schedule_date'] < $today) {
                    $childOverdue++;
                    $overdueVaccines++;
                }
                
                // Check for upcoming vaccines (within next 7 days)
                if ($vaccination['status'] === 'scheduled' && 
                    $vaccination['schedule_date'] && 
                    $vaccination['schedule_date'] >= $today && 
                    $vaccination['schedule_date'] <= $nextWeek) {
                    $childUpcoming++;
                    $upcomingVaccines++;
                }
            }
        }

        // Calculate progress percentage
        $childProgress = $totalVaccines > 0 ? round(($completedVaccines / $totalVaccines) * 100) : 0;

        $childrenData[] = [
            'baby_id' => $baby_id,
            'name' => $child['child_fname'] . ' ' . $child['child_lname'],
            'totalVaccines' => $totalVaccines,
            'completedVaccines' => $completedVaccines,
            'progress' => $childProgress,
            'overdue' => $childOverdue,
            'upcoming' => $childUpcoming,
            'completedToday' => $childCompletedToday
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'totalChildren' => $totalChildren,
            'overdueVaccines' => $overdueVaccines,
            'upcomingVaccines' => $upcomingVaccines,
            'completedToday' => $completedToday,
            'children' => $childrenData
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
