<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

$columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,date_created,date_updated,status';

// Inputs
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
$status = isset($_GET['status']) && in_array(strtolower($_GET['status']), ['pending','pendingcode']) ? strtolower($_GET['status']) : 'pending';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base where
$where = ['status' => $status];

// Fetch all matching (for search) then slice OR use paginated when no search
// Note: SupabaseConfig select supports limit and offset; we compute offset here
$offset = ($page - 1) * $limit;

if ($search !== '') {
    // OPTIMIZATION: Fetch in batches and filter, but limit total fetch to prevent excessive data
    $all = [];
    $batchSize = 200;
    $offset = 0;
    $maxFetch = 1000; // Limit total records fetched for search
    
    while (count($all) < $maxFetch) {
        $batch = supabaseSelect('child_health_records', $columns, $where, 'date_created.desc', $batchSize, $offset);
        if (!$batch || count($batch) === 0) break;
        $all = array_merge($all, $batch);
        if (count($batch) < $batchSize) break;
        $offset += $batchSize;
    }
    
    // Filter by search term
    $filtered = [];
    $q = strtolower($search);
    if (is_array($all)) {
        foreach ($all as $r) {
            $name = strtolower(($r['child_fname'] ?? '') . ' ' . ($r['child_lname'] ?? ''));
            if ($q === '' || strpos($name, $q) !== false) {
                $filtered[] = $r;
            }
        }
    }
    $total = count($filtered);
    $data = array_slice($filtered, $offset, $limit);
    $has_more = ($offset + count($data)) < $total;
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'has_more' => $has_more
    ]);
    exit;
}

// No search: use direct pagination
$totalRows = supabaseSelect('child_health_records', 'id', $where);
$total = is_array($totalRows) ? count($totalRows) : 0;
$rows = supabaseSelect('child_health_records', $columns, $where, 'date_created.desc', $limit, $offset);
$has_more = ($offset + (is_array($rows) ? count($rows) : 0)) < $total;

echo json_encode([
    'status' => 'success',
    'data' => $rows ?: [],
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'has_more' => $has_more
]);
?>


