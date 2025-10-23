<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
	echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
	exit();
}

$columns = 'id,user_id,baby_id,child_fname,child_lname,child_gender,child_birth_date,place_of_birth,mother_name,father_name,address,birth_weight,birth_height,birth_attendant,babys_card,date_created,date_updated,status,exclusive_breastfeeding_1mo,exclusive_breastfeeding_2mo,exclusive_breastfeeding_3mo,exclusive_breastfeeding_4mo,exclusive_breastfeeding_5mo,exclusive_breastfeeding_6mo,complementary_feeding_6mo,complementary_feeding_7mo,complementary_feeding_8mo,lpm,allergies';

// Inputs for pagination and search
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$purok = isset($_GET['purok']) ? trim($_GET['purok']) : '';

$where = ['status' => 'accepted'];
$offset = ($page - 1) * $limit;

if ($search !== '' || $purok !== '') {
    $all = supabaseSelect('child_health_records', $columns, $where, 'date_created.desc');
    $q = strtolower($search);
    $filtered = [];
    if (is_array($all)) {
        foreach ($all as $r) {
            $hay = strtolower(($r['child_fname'] ?? '').' '.($r['child_lname'] ?? '').' '.($r['mother_name'] ?? '').' '.($r['address'] ?? ''));
            if ($q !== '' && strpos($hay, $q) === false) { continue; }
            if ($purok !== '' && strpos(strtolower($r['address'] ?? ''), strtolower($purok)) === false) { continue; }
            $filtered[] = $r;
        }
    }
    $total = count($filtered);
    $data = array_slice($filtered, $offset, $limit);
    echo json_encode(['status' => 'success', 'data' => $data, 'total' => $total, 'page' => $page, 'limit' => $limit, 'has_more' => ($offset + count($data)) < $total]);
    exit;
}

$totalRows = supabaseSelect('child_health_records', 'id', $where);
$total = is_array($totalRows) ? count($totalRows) : 0;
$rows = supabaseSelect('child_health_records', $columns, $where, 'date_created.desc', $limit, $offset);
$has_more = ($offset + (is_array($rows) ? count($rows) : 0)) < $total;

echo json_encode(['status' => 'success', 'data' => $rows ?: [], 'total' => $total, 'page' => $page, 'limit' => $limit, 'has_more' => $has_more]);
?>


