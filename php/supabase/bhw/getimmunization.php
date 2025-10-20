<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

// Optional filters: date (all|today|tomorrow), status (all|upcoming|missed|completed), vaccine (string|all), purok (substring for address)
$dateFilter = strtolower(trim($_GET['date'] ?? 'all'));
$statusFilter = strtolower(trim($_GET['status'] ?? 'all'));
$vaccineFilter = trim($_GET['vaccine'] ?? 'all');
$purokFilter = trim($_GET['purok'] ?? '');

// Base: get child records (same columns as other handlers)
$columnsChr = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,date_created,date_updated,status';

// Keep consistent with existing page usage which lists pending child health records
$children = supabaseSelect('child_health_records', $columnsChr, ['status' => 'accepted'], 'date_created.desc');
if (!$children || !is_array($children)) { $children = []; }

// Purok filter (address contains) if provided
if ($purokFilter !== '') {
	$needle = strtolower($purokFilter);
	$children = array_values(array_filter($children, function($row) use ($needle) {
		$addr = strtolower((string)($row['address'] ?? ''));
		if ($addr === '') { return false; }
		return strpos($addr, $needle) !== false;
	}));
}

$requireSchedules = ($dateFilter !== 'all') || ($statusFilter !== 'all') || (strtolower($vaccineFilter) !== 'all');

if ($requireSchedules && !empty($children)) {
	$today = new DateTime('now', new DateTimeZone('Asia/Manila'));
	$todayStr = $today->format('Y-m-d');
	$tomorrow = (clone $today)->modify('+1 day');
	$tomorrowStr = $tomorrow->format('Y-m-d');

	$children = array_values(array_filter($children, function($row) use ($dateFilter, $statusFilter, $vaccineFilter, $todayStr, $tomorrowStr) {
		$babyId = $row['baby_id'] ?? '';
		if ($babyId === '') { return false; }
		// Fetch schedules for this baby
		$columns = 'id,baby_id,vaccine_name,dose_number,status,schedule_date,date_given,catch_up_date,weight,height,temperature,created_at';
		$schedules = supabaseSelect('immunization_records', $columns, ['baby_id' => $babyId], 'schedule_date.asc');
		if (!$schedules || !is_array($schedules)) { $schedules = []; }

		// Vaccine filter
		if (strtolower($vaccineFilter) !== 'all') {
			$hasVaccine = false;
			foreach ($schedules as $s) {
				if ((string)($s['vaccine_name'] ?? '') === $vaccineFilter) { $hasVaccine = true; break; }
			}
			if (!$hasVaccine) { return false; }
		}

		// Date filter uses not-completed and catch_up_date equals today/tomorrow
		if ($dateFilter === 'today') {
			$ok = false;
			foreach ($schedules as $s) {
				$status = strtolower((string)($s['status'] ?? ''));
				if ($status === 'completed') { continue; }
				if (($s['catch_up_date'] ?? '') === $todayStr) { $ok = true; break; }
			}
			if (!$ok) { return false; }
		} elseif ($dateFilter === 'tomorrow') {
			$ok = false;
			foreach ($schedules as $s) {
				$status = strtolower((string)($s['status'] ?? ''));
				if ($status === 'completed') { continue; }
				if (($s['catch_up_date'] ?? '') === $tomorrowStr) { $ok = true; break; }
			}
			if (!$ok) { return false; }
		}

		// Status filter
		if ($statusFilter === 'upcoming') {
			$ok = false;
			foreach ($schedules as $s) {
				$status = strtolower((string)($s['status'] ?? ''));
				if ($status === 'completed') { continue; }
				$due = (string)($s['catch_up_date'] ?? '');
				if ($due !== '' && $due >= $todayStr) { $ok = true; break; }
			}
			if (!$ok) { return false; }
		} elseif ($statusFilter === 'missed') {
			$ok = false;
			foreach ($schedules as $s) {
				$status = strtolower((string)($s['status'] ?? ''));
				if ($status === 'completed') { continue; }
				$due = (string)($s['catch_up_date'] ?? '');
				if ($due !== '' && $due < $todayStr) { $ok = true; break; }
			}
			if (!$ok) { return false; }
		} elseif ($statusFilter === 'completed') {
			$ok = false;
			foreach ($schedules as $s) {
				if (strtolower((string)($s['status'] ?? '')) === 'completed') { $ok = true; break; }
			}
			if (!$ok) { return false; }
		}

		return true;
	}));
}

echo json_encode(['status' => 'success', 'data' => $children]);
?>



