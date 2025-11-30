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
$requestedBabyId = isset($_GET['baby_id']) ? trim($_GET['baby_id']) : '';

if ($requestedBabyId !== '') {
	$children = supabaseSelect(
		'child_health_records',
		'baby_id,child_fname,child_lname',
		['user_id' => $user_id, 'status' => 'accepted', 'baby_id' => $requestedBabyId],
		null,
		1
	);
} else {
	$children = supabaseSelect(
		'child_health_records', 
		'baby_id,child_fname,child_lname', 
		['user_id' => $user_id, 'status' => 'accepted']
	);
}

    if (!$children) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    $immunization_records = [];

    // Get all immunization records for all children
foreach ($baby_ids as $baby_id) {
        // Fetch all records without ordering first to ensure we get all vaccines including IPV
        $vaccinations = supabaseSelect(
            'immunization_records',
            'id,baby_id,vaccine_name,dose_number,schedule_date,batch_schedule_date,catch_up_date,date_given,status,height,weight,muac,remarks',
            ['baby_id' => $baby_id]
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
                    'batch_schedule_date' => $vaccination['batch_schedule_date'],
                    'catch_up_date' => $vaccination['catch_up_date'],
                    'date_given' => $vaccination['date_given'],
                    'status' => $vaccination['status'],
                    'height' => $vaccination['height'] ?? null,
                    'weight' => $vaccination['weight'] ?? null,
                    'muac' => $vaccination['muac'] ?? null,
                    'remarks' => $vaccination['remarks'] ?? null
                ];
            }
        }
    }

    // Sort by child name, then by schedule date (handle NULLs properly)
    usort($immunization_records, function($a, $b) {
        if ($a['child_name'] === $b['child_name']) {
            $aDate = $a['batch_schedule_date'] ?? $a['schedule_date'] ?? $a['catch_up_date'] ?? '';
            $bDate = $b['batch_schedule_date'] ?? $b['schedule_date'] ?? $b['catch_up_date'] ?? '';
            // Handle empty dates - put them at the end
            if ($aDate === '' && $bDate === '') return 0;
            if ($aDate === '') return 1;
            if ($bDate === '') return -1;
            return strcmp($aDate, $bDate);
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
