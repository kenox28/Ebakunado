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
	// Get all accepted children with feeding status
$children = supabaseSelect('child_health_records', 'id,user_id,baby_id,child_fname,child_lname,address,exclusive_breastfeeding_1mo,exclusive_breastfeeding_2mo,exclusive_breastfeeding_3mo,exclusive_breastfeeding_4mo,exclusive_breastfeeding_5mo,exclusive_breastfeeding_6mo,complementary_feeding_6mo,complementary_feeding_7mo,complementary_feeding_8mo,mother_td_dose1_date,mother_td_dose2_date,mother_td_dose3_date,mother_td_dose4_date,mother_td_dose5_date', ['status' => 'accepted'], 'child_fname.asc');
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
				'status' => '',
				// Feeding status
				'exclusive_breastfeeding_1mo' => $child['exclusive_breastfeeding_1mo'] ?? false,
				'exclusive_breastfeeding_2mo' => $child['exclusive_breastfeeding_2mo'] ?? false,
				'exclusive_breastfeeding_3mo' => $child['exclusive_breastfeeding_3mo'] ?? false,
				'exclusive_breastfeeding_4mo' => $child['exclusive_breastfeeding_4mo'] ?? false,
				'exclusive_breastfeeding_5mo' => $child['exclusive_breastfeeding_5mo'] ?? false,
				'exclusive_breastfeeding_6mo' => $child['exclusive_breastfeeding_6mo'] ?? false,
				'complementary_feeding_6mo' => $child['complementary_feeding_6mo'] ?? '',
				'complementary_feeding_7mo' => $child['complementary_feeding_7mo'] ?? '',
				'complementary_feeding_8mo' => $child['complementary_feeding_8mo'] ?? '',
				// Mother's TD Status
				'mother_td_dose1_date' => $child['mother_td_dose1_date'] ?? '',
				'mother_td_dose2_date' => $child['mother_td_dose2_date'] ?? '',
				'mother_td_dose3_date' => $child['mother_td_dose3_date'] ?? '',
				'mother_td_dose4_date' => $child['mother_td_dose4_date'] ?? '',
				'mother_td_dose5_date' => $child['mother_td_dose5_date'] ?? ''
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
					'status' => $immunization['status'],
					// Feeding status
					'exclusive_breastfeeding_1mo' => $child['exclusive_breastfeeding_1mo'] ?? false,
					'exclusive_breastfeeding_2mo' => $child['exclusive_breastfeeding_2mo'] ?? false,
					'exclusive_breastfeeding_3mo' => $child['exclusive_breastfeeding_3mo'] ?? false,
					'exclusive_breastfeeding_4mo' => $child['exclusive_breastfeeding_4mo'] ?? false,
					'exclusive_breastfeeding_5mo' => $child['exclusive_breastfeeding_5mo'] ?? false,
					'exclusive_breastfeeding_6mo' => $child['exclusive_breastfeeding_6mo'] ?? false,
					'complementary_feeding_6mo' => $child['complementary_feeding_6mo'] ?? '',
					'complementary_feeding_7mo' => $child['complementary_feeding_7mo'] ?? '',
					'complementary_feeding_8mo' => $child['complementary_feeding_8mo'] ?? '',
					// Mother's TD Status
					'mother_td_dose1_date' => $child['mother_td_dose1_date'] ?? '',
					'mother_td_dose2_date' => $child['mother_td_dose2_date'] ?? '',
					'mother_td_dose3_date' => $child['mother_td_dose3_date'] ?? '',
					'mother_td_dose4_date' => $child['mother_td_dose4_date'] ?? '',
					'mother_td_dose5_date' => $child['mother_td_dose5_date'] ?? ''
				];
			}
		}
	}
	
	echo json_encode(['status' => 'success', 'data' => $rows]);
	
} catch (Exception $e) {
	echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
