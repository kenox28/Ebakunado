<?php
session_start();

if (!isset($_SESSION['super_admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

$limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$search = strtolower(trim($_GET['search'] ?? ''));
$offset = ($page - 1) * $limit;

$allLogs = supabaseSelect('activity_logs', '*', [], 'created_at.desc') ?: [];

if ($search !== '') {
    $allLogs = array_values(array_filter($allLogs, function ($row) use ($search) {
        $columns = [
            'log_id',
            'user_id',
            'user_type',
            'action_type',
            'description',
            'ip_address'
        ];
        foreach ($columns as $col) {
            if (strpos(strtolower((string) ($row[$col] ?? '')), $search) !== false) {
                return true;
            }
        }
        return false;
    }));
}

$total = count($allLogs);
$totalPages = $total > 0 ? ceil($total / $limit) : 1;
if ($page > $totalPages) {
    $page = $totalPages;
}

$logs = array_slice($allLogs, ($page - 1) * $limit, $limit);
$hasMore = $total > ($page * $limit);

echo json_encode([
    'status' => 'success',
    'data' => $logs,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'has_more' => $hasMore
]);

