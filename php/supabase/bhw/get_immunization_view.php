<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id'])) {
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

try {
	// Get all accepted children
$children = supabaseSelect('child_health_records', 'id,user_id,baby_id,child_fname,child_lname,address', ['status' => 'accepted'], 'child_fname.asc');
	if (!$children) $children = [];
	
    // Get all immunization records (include id and dose_number for actions)
    $immunizations = supabaseSelect('immunization_records', 'id,baby_id,vaccine_name,dose_number,schedule_date,catch_up_date,date_given,status', [], 'schedule_date.asc');
	if (!$immunizations) $immunizations = [];
	
	// Group immunizations by baby_id
	$immunizationsByBaby = [];
	foreach ($immunizations as $immunization) {
		$babyId = $immunization['baby_id'];
		if (!isset($immunizationsByBaby[$babyId])) {
			$immunizationsByBaby[$babyId] = [];
		}
		$immunizationsByBaby[$babyId][] = $immunization;
	}
	
	// Create rows - one per vaccine per child
	$rows = [];
	foreach ($children as $child) {
		$babyId = $child['baby_id'];
		$childImmunizations = $immunizationsByBaby[$babyId] ?? [];
		
		if (empty($childImmunizations)) {
			// Child with no vaccines - show one row
			$rows[] = [
				'id' => $child['id'],
				'user_id' => $child['user_id'],
				'baby_id' => $child['baby_id'],
				'child_fname' => $child['child_fname'],
				'child_lname' => $child['child_lname'],
				'address' => $child['address'],
				'vaccine_name' => '',
				'schedule_date' => '',
				'catch_up_date' => '',
				'status' => ''
			];
		} else {
			// Child with vaccines - show one row per vaccine
            foreach ($childImmunizations as $immunization) {
				$rows[] = [
                    // child record id
                    'id' => $child['id'],
                    // immunization record id for actions
                    'immunization_id' => $immunization['id'] ?? null,
					'user_id' => $child['user_id'],
					'baby_id' => $child['baby_id'],
					'child_fname' => $child['child_fname'],
					'child_lname' => $child['child_lname'],
					'address' => $child['address'],
					'vaccine_name' => $immunization['vaccine_name'],
                    'dose_number' => $immunization['dose_number'] ?? null,
					'schedule_date' => $immunization['schedule_date'],
					'catch_up_date' => $immunization['catch_up_date'],
                    'date_given' => $immunization['date_given'] ?? null,
					'status' => $immunization['status']
				];
			}
		}
	}
	
	echo json_encode(['status' => 'success', 'data' => $rows]);
	
} catch (Exception $e) {
	echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
