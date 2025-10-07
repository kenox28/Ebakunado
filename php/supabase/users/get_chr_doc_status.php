<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$baby_id = $_GET['baby_id'] ?? '';
$request_type = strtolower(trim($_GET['request_type'] ?? ''));
if ($baby_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing baby_id']);
    exit();
}

$where = ['user_id' => $user_id, 'baby_id' => $baby_id];
if ($request_type !== '') { $where['request_type'] = $request_type; }
$rows = supabaseSelect('chrdocrequest', '*', $where, 'created_at.desc', 1);
$row = ($rows && count($rows) > 0) ? $rows[0] : null;
echo json_encode(['status' => 'success', 'data' => $row, 'request_type' => $request_type]);
exit();
?>


