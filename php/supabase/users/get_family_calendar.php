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
$month = $_GET['month'] ?? date('n'); // Current month if not specified
$year = $_GET['year'] ?? date('Y'); // Current year if not specified

try {
    // Get all accepted children for this user
    $children = supabaseSelect(
        'child_health_records', 
        'baby_id,child_fname,child_lname', 
        ['user_id' => $user_id, 'status' => 'accepted']
    );

    if (!$children) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    
    // Get vaccination schedules for the specified month
    $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
    $endDate = date('Y-m-t', strtotime($startDate)); // Last day of month
    
    $vaccinations = supabaseSelect(
        'immunization_records',
        'baby_id,vaccine_name,dose_number,status,schedule_date,date_given',
        ['baby_id' => $baby_ids]
    );

    $calendarData = [];
    $childNames = [];
    
    // Create child name lookup
    foreach ($children as $child) {
        $childNames[$child['baby_id']] = $child['child_fname'] . ' ' . $child['child_lname'];
    }

    if ($vaccinations) {
        foreach ($vaccinations as $vaccination) {
            $date = $vaccination['schedule_date'] ?: $vaccination['date_given'];
            
            if (!$date) continue;
            
            // Check if this vaccination falls within the requested month
            if ($date >= $startDate && $date <= $endDate) {
                $day = (int)date('j', strtotime($date));
                
                if (!isset($calendarData[$day])) {
                    $calendarData[$day] = [];
                }
                
                $calendarData[$day][] = [
                    'baby_id' => $vaccination['baby_id'],
                    'child_name' => $childNames[$vaccination['baby_id']] ?? 'Unknown',
                    'vaccine_name' => $vaccination['vaccine_name'],
                    'dose_number' => $vaccination['dose_number'],
                    'status' => $vaccination['status'],
                    'date' => $date,
                    'type' => $vaccination['date_given'] ? 'completed' : 'scheduled'
                ];
            }
        }
    }

    // Sort vaccinations within each day by time (if available)
    foreach ($calendarData as $day => $vaccinations) {
        usort($calendarData[$day], function($a, $b) {
            return strcmp($a['vaccine_name'], $b['vaccine_name']);
        });
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'month' => $month,
            'year' => $year,
            'calendar' => $calendarData
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
