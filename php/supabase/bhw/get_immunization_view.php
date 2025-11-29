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
	// Get all accepted children with feeding status
	$children = supabaseSelect('child_health_records', 'id,user_id,baby_id,child_fname,child_lname,address,exclusive_breastfeeding_1mo,exclusive_breastfeeding_2mo,exclusive_breastfeeding_3mo,exclusive_breastfeeding_4mo,exclusive_breastfeeding_5mo,exclusive_breastfeeding_6mo,complementary_feeding_6mo,complementary_feeding_7mo,complementary_feeding_8mo', ['status' => 'accepted'], 'child_fname.asc');
	if (!$children) $children = [];
	
	// Get baby_ids of accepted children (CRITICAL: only fetch records for accepted children)
	$acceptedBabyIds = array_column($children, 'baby_id');
	if (empty($acceptedBabyIds)) {
		// No accepted children, return empty
		echo json_encode([
			'status' => 'success',
			'data' => [],
			'total' => 0,
			'page' => 1,
			'limit' => 10,
			'has_more' => false
		]);
		exit();
	}

	// Read filters first
	$dateSel = isset($_GET['date']) ? trim($_GET['date']) : '';
	$statusSel = isset($_GET['status']) ? trim($_GET['status']) : 'all';
	$vaccineSel = isset($_GET['vaccine']) ? trim($_GET['vaccine']) : 'all';
	$purokQ = isset($_GET['purok']) ? strtolower(trim($_GET['purok'])) : '';
$nameQ = isset($_GET['name']) ? strtolower(trim($_GET['name'])) : '';
	$todayStr = date('Y-m-d');
$isFilterActive = ($dateSel !== '' || $statusSel !== 'all' || $vaccineSel !== 'all' || $purokQ !== '' || $nameQ !== '');

	// Pagination params
	$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
	$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

	// OPTIMIZATION: Use database filters, but ALWAYS filter by accepted children's baby_ids
	$immunizations = [];
	$conditions = [];
	
	// CRITICAL: Always filter by accepted children's baby_ids
	$conditions['baby_id'] = $acceptedBabyIds;
	
	// Apply database-level filters (much faster than PHP filtering)
	if ($vaccineSel !== 'all') { 
		$conditions['vaccine_name'] = $vaccineSel; 
	}
	
	// Status filter at database level (status is "scheduled", "missed", or "taken", NOT "upcoming")
	if ($statusSel === 'completed') { 
		$conditions['status'] = 'taken'; 
	} elseif ($statusSel === 'missed') {
		$conditions['status'] = 'missed';
	} elseif ($statusSel === 'upcoming') {
		// "upcoming" means status="scheduled" with dates >= today
		// We'll filter by status first, then filter dates in PHP (since we need to check batch_schedule_date too)
		$conditions['status'] = 'scheduled';
	}
	
	if ($isFilterActive && $dateSel === '' && $statusSel !== 'upcoming') {
		// When filters are active but no date filter and not "upcoming", use pagination with database filters
		$offset = ($page - 1) * $limit;
		$immunizations = supabaseSelect(
			'immunization_records',
			'id,baby_id,vaccine_name,dose_number,schedule_date,batch_schedule_date,catch_up_date,date_given,status,created_at',
			$conditions,
			'schedule_date.asc',
			$limit * 3, // Fetch more to account for filtering
			$offset
		);
		if (!$immunizations) $immunizations = [];
	} else {
		// For date filter, "upcoming" filter, or no filters, fetch in batches
		$batchSize = 200;
		$offset = 0;
		while (true) {
			$batch = supabaseSelect(
				'immunization_records',
				'id,baby_id,vaccine_name,dose_number,schedule_date,batch_schedule_date,catch_up_date,date_given,status,created_at',
				$conditions,
				'schedule_date.asc',
				$batchSize,
				$offset
			);
			if (!$batch || count($batch) === 0) break;
			$immunizations = array_merge($immunizations, $batch);
			if (count($batch) < $batchSize) break;
			$offset += $batchSize;
			// Limit total fetch to prevent excessive data
			if (count($immunizations) > 2000) break;
		}
	}

    $immsFiltered = array_values(array_filter($immunizations, function($r) use ($dateSel, $statusSel, $vaccineSel, $todayStr) {
        // Vaccine filter (already filtered in DB, but double-check)
        if ($vaccineSel !== 'all') {
            if ((string)($r['vaccine_name'] ?? '') !== $vaccineSel) return false;
        }
        // Date filter: either schedule or catch-up exactly matches
        if ($dateSel !== '') {
            $sd = (string)($r['schedule_date'] ?? '');
            $bd = (string)($r['batch_schedule_date'] ?? '');
            $cd = (string)($r['catch_up_date'] ?? '');
            if ($sd !== $dateSel && $bd !== $dateSel && $cd !== $dateSel) return false;
        }
        // Status filter - "upcoming" means scheduled with operational date >= today
        if ($statusSel !== 'all') {
            $status = strtolower((string)($r['status'] ?? ''));
            // For "upcoming", use batch_schedule_date if available, otherwise schedule_date
            $operationalDate = (string)($r['batch_schedule_date'] ?? ($r['schedule_date'] ?? ''));
            
            if ($statusSel === 'upcoming') {
                // Show only scheduled items with operational date >= today
                if ($status !== 'scheduled') return false;
                if ($operationalDate === '' || $operationalDate < $todayStr) return false;
            } else if ($statusSel === 'missed') {
                // Missed: past due date, not taken
                if ($operationalDate === '' || $operationalDate >= $todayStr || $status === 'taken') return false;
            } else if ($statusSel === 'completed') {
                if ($status !== 'taken') return false;
            }
        }
        return true;
    }));

    // Gather child data for the filtered immunizations
    $babyIds = array_values(array_unique(array_map(function($r){ return $r['baby_id']; }, $immsFiltered)));
    $childrenByBaby = [];
    if (!empty($babyIds)) {
        $children = supabaseSelect('child_health_records', 'id,user_id,baby_id,child_fname,child_lname,address,exclusive_breastfeeding_1mo,exclusive_breastfeeding_2mo,exclusive_breastfeeding_3mo,exclusive_breastfeeding_4mo,exclusive_breastfeeding_5mo,exclusive_breastfeeding_6mo,complementary_feeding_6mo,complementary_feeding_7mo,complementary_feeding_8mo', ['status' => 'accepted', 'baby_id' => $babyIds], null, null);
        if ($children) {
            foreach ($children as $c) { $childrenByBaby[$c['baby_id']] = $c; }
        }
    }

    // Batch fetch mother's TD doses for all related user_ids
    $userIds = array_values(array_unique(array_map(function($bId) use ($childrenByBaby){ return $childrenByBaby[$bId]['user_id'] ?? null; }, $babyIds)));
    $userIds = array_values(array_filter($userIds, function($v){ return !empty($v); }));
    $tdByUser = [];
    if (!empty($userIds)) {
        $tdRows = supabaseSelect('mother_tetanus_doses','user_id,dose1_date,dose2_date,dose3_date,dose4_date,dose5_date', ['user_id' => $userIds], null, null);
        if ($tdRows) { foreach ($tdRows as $t) { $tdByUser[$t['user_id']] = $t; } }
    }

    // Build output rows (only immunizations with accepted children)
    $rows = [];
    foreach ($immsFiltered as $imm) {
        $child = $childrenByBaby[$imm['baby_id']] ?? null;
        if (!$child) continue; // skip if child is not accepted
        $td = $tdByUser[$child['user_id']] ?? [];
			$rows[] = [
				'id' => $child['id'],
            'immunization_id' => $imm['id'] ?? null,
				'user_id' => $child['user_id'],
				'baby_id' => $child['baby_id'],
				'child_fname' => $child['child_fname'],
				'child_lname' => $child['child_lname'],
				'address' => $child['address'],
            'vaccine_name' => $imm['vaccine_name'],
            'dose_number' => $imm['dose_number'] ?? null,
            'schedule_date' => $imm['schedule_date'],
            'batch_schedule_date' => $imm['batch_schedule_date'],
            'catch_up_date' => $imm['catch_up_date'],
            'date_given' => $imm['date_given'] ?? null,
            'status' => $imm['status'],
				'exclusive_breastfeeding_1mo' => $child['exclusive_breastfeeding_1mo'] ?? false,
				'exclusive_breastfeeding_2mo' => $child['exclusive_breastfeeding_2mo'] ?? false,
				'exclusive_breastfeeding_3mo' => $child['exclusive_breastfeeding_3mo'] ?? false,
				'exclusive_breastfeeding_4mo' => $child['exclusive_breastfeeding_4mo'] ?? false,
				'exclusive_breastfeeding_5mo' => $child['exclusive_breastfeeding_5mo'] ?? false,
				'exclusive_breastfeeding_6mo' => $child['exclusive_breastfeeding_6mo'] ?? false,
				'complementary_feeding_6mo' => $child['complementary_feeding_6mo'] ?? '',
				'complementary_feeding_7mo' => $child['complementary_feeding_7mo'] ?? '',
				'complementary_feeding_8mo' => $child['complementary_feeding_8mo'] ?? '',
            'mother_td_dose1_date' => $td['dose1_date'] ?? '',
            'mother_td_dose2_date' => $td['dose2_date'] ?? '',
            'mother_td_dose3_date' => $td['dose3_date'] ?? '',
            'mother_td_dose4_date' => $td['dose4_date'] ?? '',
            'mother_td_dose5_date' => $td['dose5_date'] ?? ''
        ];
    }

    // Optional: apply purok filter (address contains), after join
    if ($purokQ !== '') {
        $rows = array_values(array_filter($rows, function($r) use ($purokQ){
            $addr = strtolower((string)($r['address'] ?? ''));
            return strpos($addr, $purokQ) !== false;
        }));
    }

    // Optional: apply name search (first, last, or full)
    if ($nameQ !== '') {
        $rows = array_values(array_filter($rows, function($r) use ($nameQ) {
            $first = strtolower((string)($r['child_fname'] ?? ''));
            $last = strtolower((string)($r['child_lname'] ?? ''));
            $full = trim($first . ' ' . $last);
            return strpos($first, $nameQ) !== false
                || strpos($last, $nameQ) !== false
                || strpos($full, $nameQ) !== false;
        }));
    }

    // Final slice for page and compute next availability
    if ($isFilterActive) {
        $skip = ($page - 1) * $limit;
        $totalFiltered = count($rows);
        $has_more = $totalFiltered > ($skip + $limit);
        $paged = array_slice($rows, $skip, $limit);
    } else {
        $has_more = count($rows) > $limit;
        $paged = array_slice($rows, 0, $limit);
    }

	echo json_encode([
		'status' => 'success',
		'data' => $paged,
		'total' => $isFilterActive ? $totalFiltered : null,
        'page' => $page,
        'limit' => $limit,
        'has_more' => $has_more
	]);
	
} catch (Exception $e) {
	echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
