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
        echo json_encode(['status' => 'success', 'data' => []]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    $immunization_records = [];

    // Get all immunization records for all children
    foreach ($baby_ids as $baby_id) {
        $vaccinations = supabaseSelect(
            'immunization_records',
            'id,baby_id,vaccine_name,dose_number,schedule_date,date_given,status',
            ['baby_id' => $baby_id],
            'schedule_date.asc'
        );

        if ($vaccinations) {
            foreach ($vaccinations as $vaccination) {
                // Find child name
                $child_name = '';
                foreach ($children as $child) {
                    if ($child['baby_id'] === $baby_id) {
                        $child_name = $child['child_fname'] . ' ' . $child['child_lname'];
                        break;
                    }
                }

                $immunization_records[] = [
                    'id' => $vaccination['id'],
                    'baby_id' => $baby_id,
                    'child_name' => $child_name,
                    'vaccine_name' => $vaccination['vaccine_name'],
                    'dose_number' => $vaccination['dose_number'],
                    'schedule_date' => $vaccination['schedule_date'],
                    'date_given' => $vaccination['date_given'],
                    'status' => $vaccination['status']
                ];
            }
        }
    }

    // Sort by child name, then by schedule date
    usort($immunization_records, function($a, $b) {
        if ($a['child_name'] === $b['child_name']) {
            return strtotime($a['schedule_date']) - strtotime($b['schedule_date']);
        }
        return strcmp($a['child_name'], $b['child_name']);
    });

    echo json_encode([
        'status' => 'success',
        'data' => $immunization_records
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
