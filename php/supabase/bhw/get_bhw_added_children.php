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

// Base where: Filter for BHW/Midwife-added children (user_id starts with 'FAM-')
// We need to fetch all records and filter by user_id pattern since Supabase doesn't support LIKE directly
$offset = ($page - 1) * $limit;

// Fetch all records that match status first
$allRecords = supabaseSelect('child_health_records', $columns, ['status' => $status], 'date_created.desc');

// Filter for BHW/Midwife-added children (user_id starts with 'FAM-')
$filteredRecords = [];
if (is_array($allRecords)) {
    foreach ($allRecords as $record) {
        $user_id = $record['user_id'] ?? '';
        // Check if user_id starts with 'FAM-' (BHW/Midwife-added)
        if (strpos($user_id, 'FAM-') === 0) {
            // Apply search filter if provided
            if ($search !== '') {
                $q = strtolower($search);
                $name = strtolower(($record['child_fname'] ?? '') . ' ' . ($record['child_lname'] ?? ''));
                if (strpos($name, $q) === false) {
                    continue; // Skip if doesn't match search
                }
            }
            $filteredRecords[] = $record;
        }
    }
}

$total = count($filteredRecords);
$data = array_slice($filteredRecords, $offset, $limit);
$has_more = ($offset + count($data)) < $total;

echo json_encode([
    'status' => 'success',
    'data' => $data,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'has_more' => $has_more
]);
?>

