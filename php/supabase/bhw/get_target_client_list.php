<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

try {
	// Read filters and pagination
	$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
	$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all'; // SCHEDULED | MISSED | TRANSFERRED | all
	$purokQ = isset($_GET['purok']) ? strtolower(trim($_GET['purok'])) : '';
	$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

	$skip = ($page - 1) * $limit;
	$take = $limit;

	// Batch through children and compute TCL rows; apply filters; then skip/take
	$rows = [];
	$matchedCount = 0;
	$totalFiltered = 0;
	$has_more = false;

	$batchSize = 200;
	$offset = 0;
	while (true) {
		$children = supabaseSelect(
			'child_health_records',
			'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,mother_name,father_name,address,birth_weight,birth_height,status',
			['status' => 'accepted'],
			'child_fname.asc',
			$batchSize,
			$offset
		);
		if (!$children || count($children) === 0) { break; }

		// Fetch phone numbers for users in this batch (optional)
		$userIds = array_values(array_unique(array_map(function($c){ return $c['user_id']; }, $children)));
		$userPhoneById = [];
		if (!empty($userIds)) {
			$users = supabaseSelect('users', 'user_id,phone_number', ['user_id' => $userIds], null, null);
			if ($users) { foreach ($users as $u) { $userPhoneById[$u['user_id']] = $u['phone_number'] ?? ''; } }
		}

		foreach ($children as $idx => $child) {
			// Build vaccination status per child
			$vac = [
				'BCG' => '',
				'HEPAB1_w_in_24hrs' => '',
				'HEPAB1_more_than_24hrs' => '',
				'Penta 1' => '', 'Penta 2' => '', 'Penta 3' => '',
				'OPV 1' => '', 'OPV 2' => '', 'OPV 3' => '',
				'Rota 1' => '', 'Rota 2' => '',
				'PCV 1' => '', 'PCV 2' => '', 'PCV 3' => '',
				'MCV1_AMV' => '', 'MCV2_MMR' => ''
			];
			$immRecs = supabaseSelect(
				'immunization_records',
				'vaccine_name,dose_number,status,schedule_date,date_given,catch_up_date,weight,height',
				['baby_id' => $child['baby_id']],
				'schedule_date.asc'
			);
			if ($immRecs) {
				foreach ($immRecs as $rec) {
					$vk = '';
					if ($rec['vaccine_name'] === 'BCG') { $vk = 'BCG'; }
					elseif ($rec['vaccine_name'] === 'HEPAB1 (w/in 24 hrs)') { $vk = 'HEPAB1_w_in_24hrs'; }
					elseif ($rec['vaccine_name'] === 'HEPAB1 (More than 24hrs)') { $vk = 'HEPAB1_more_than_24hrs'; }
					elseif ($rec['vaccine_name'] === 'Pentavalent (DPT-HepB-Hib) - 1st') { $vk = 'Penta 1'; }
					elseif ($rec['vaccine_name'] === 'Pentavalent (DPT-HepB-Hib) - 2nd') { $vk = 'Penta 2'; }
					elseif ($rec['vaccine_name'] === 'Pentavalent (DPT-HepB-Hib) - 3rd') { $vk = 'Penta 3'; }
					elseif ($rec['vaccine_name'] === 'OPV - 1st') { $vk = 'OPV 1'; }
					elseif ($rec['vaccine_name'] === 'OPV - 2nd') { $vk = 'OPV 2'; }
					elseif ($rec['vaccine_name'] === 'OPV - 3rd') { $vk = 'OPV 3'; }
					elseif ($rec['vaccine_name'] === 'Rota Virus Vaccine - 1st') { $vk = 'Rota 1'; }
					elseif ($rec['vaccine_name'] === 'Rota Virus Vaccine - 2nd') { $vk = 'Rota 2'; }
					elseif ($rec['vaccine_name'] === 'PCV - 1st') { $vk = 'PCV 1'; }
					elseif ($rec['vaccine_name'] === 'PCV - 2nd') { $vk = 'PCV 2'; }
					elseif ($rec['vaccine_name'] === 'PCV - 3rd') { $vk = 'PCV 3'; }
					elseif ($rec['vaccine_name'] === 'MCV1 (AMV)') { $vk = 'MCV1_AMV'; }
					elseif ($rec['vaccine_name'] === 'MCV2 (MMR)') { $vk = 'MCV2_MMR'; }
					if ($vk === '') { continue; }
					$display = '';
					if (($rec['status'] === 'taken' || $rec['status'] === 'completed') && !empty($rec['date_given'])) {
						$ts = strtotime($rec['date_given']);
						$display = '✓ ' . ($ts ? date('m/d/Y', $ts) : $rec['date_given']);
					} elseif ($rec['status'] === 'missed') {
						$cu = !empty($rec['catch_up_date']) ? strtotime($rec['catch_up_date']) : false;
						$display = '✗ ' . ($cu ? date('m/d/Y', $cu) : ($rec['catch_up_date'] ?? ''));
					} elseif ($rec['status'] === 'scheduled' || $rec['status'] === 'pending') {
						$display = 'SCHEDULED';
					}
					$vac[$vk] = $display;
				}
			}

			// Overall status
			$missed = 0; $sched = 0; $completed = 0;
			foreach ($vac as $st) {
				if (strpos($st, '✗') !== false) $missed++;
				elseif ($st === 'SCHEDULED') $sched++;
				elseif (strpos($st, '✓') !== false) $completed++;
			}
			$overall = 'SCHEDULED';
			if ($missed > 0) $overall = 'MISSED';
			elseif ($sched === 0) $overall = 'TRANSFERRED';

			// Build row
			$row = [
				'id' => $child['id'],
				'child_name' => trim(($child['child_fname'] ?? '') . ' ' . ($child['child_lname'] ?? '')),
				'sex' => $child['child_gender'] ?? '',
				'date_of_birth' => date('m/d/Y', strtotime($child['child_birth_date'])),
				'mother_name' => $child['mother_name'] ?? '',
				'address' => $child['address'] ?? '',
				'BCG' => $vac['BCG'],
				'HEPAB1_w_in_24hrs' => $vac['HEPAB1_w_in_24hrs'],
				'HEPAB1_more_than_24hrs' => $vac['HEPAB1_more_than_24hrs'],
				'Penta 1' => $vac['Penta 1'], 'Penta 2' => $vac['Penta 2'], 'Penta 3' => $vac['Penta 3'],
				'OPV 1' => $vac['OPV 1'], 'OPV 2' => $vac['OPV 2'], 'OPV 3' => $vac['OPV 3'],
				'Rota 1' => $vac['Rota 1'], 'Rota 2' => $vac['Rota 2'],
				'PCV 1' => $vac['PCV 1'], 'PCV 2' => $vac['PCV 2'], 'PCV 3' => $vac['PCV 3'],
				'MCV1_AMV' => $vac['MCV1_AMV'], 'MCV2_MMR' => $vac['MCV2_MMR'],
				'weight' => $child['birth_weight'] ?? '',
				'height' => $child['birth_height'] ?? '',
				'status' => $overall,
				'remarks' => ($missed > 0 ? $missed . ' vaccine(s) missed' : ($sched > 0 ? $sched . ' vaccine(s) scheduled' : 'All vaccines completed')),
				'baby_id' => $child['baby_id'],
				'user_id' => $child['user_id'],
				'phone_number' => $userPhoneById[$child['user_id']] ?? ''
			];

			// Apply filters
			if ($search !== '') {
				$needle = $search;
				$hay = strtolower(($row['child_name'] ?? '') . ' ' . ($row['mother_name'] ?? '') . ' ' . ($row['address'] ?? '') . ' ' . ($row['baby_id'] ?? ''));
				if (strpos($hay, $needle) === false) continue;
			}
			if ($statusFilter !== 'all' && strtoupper($row['status']) !== strtoupper($statusFilter)) continue;
			if ($purokQ !== '' && strpos(strtolower($row['address'] ?? ''), $purokQ) === false) continue;

			// Count match
			$totalFiltered++;
			if ($totalFiltered <= $skip) { continue; }
			if (count($rows) >= $take) { $has_more = true; break 2; }
			$rows[] = $row;
		}

		$offset += $batchSize;
		if (count($children) < $batchSize) break;
	}

    // Robust has_more computation in case loop ended without early break
    $has_more = $has_more || ($totalFiltered > ($skip + count($rows)));

	echo json_encode([
		'status' => 'success',
		'data' => $rows,
		'total' => $totalFiltered,
		'page' => $page,
		'limit' => $limit,
		'has_more' => $has_more
	]);

} catch (Exception $e) {
	echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>


