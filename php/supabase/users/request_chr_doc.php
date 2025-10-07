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
$baby_id = $_POST['baby_id'] ?? '';
$request_type = strtolower(trim($_POST['request_type'] ?? ''));
if ($baby_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing baby_id']);
    exit();
}

// Validate request_type: transfer | school
if ($request_type === '' || !in_array($request_type, ['transfer','school'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request_type']);
    exit();
}

// Insert request into chrdocrequest table with status=pendingCHR
$insert = supabaseInsert('chrdocrequest', [
    'user_id' => $user_id,
    'baby_id' => $baby_id,
    'status' => 'pendingCHR',
    'request_type' => $request_type,
    'doc_url' => null,
    'created_at' => date('Y-m-d H:i:s')
]);

if ($insert === false) {
    $err = isset($supabase) && method_exists($supabase, 'getLastError') ? $supabase->getLastError() : null;
    echo json_encode(['status' => 'error', 'message' => 'Request failed', 'debug' => $err]);
    exit();
}

echo json_encode(['status' => 'success', 'message' => 'Request submitted', 'data' => $insert]);
exit();
?>


