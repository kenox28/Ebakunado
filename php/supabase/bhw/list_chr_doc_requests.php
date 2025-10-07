<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

if (!isset($_SESSION['bhw_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$rows = supabaseSelect('chrdocrequest', '*', ['status' => 'pendingCHR'], 'created_at.asc');
echo json_encode(['status' => 'success', 'data' => $rows ?: []]);
exit();
?>


