<?php
session_start();

// Check if admin or super admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

    $columns = 'log_id,user_id,user_type,action_type,description,ip_address,created_at';
    $logs = supabaseSelect('activity_logs', $columns, [], 'created_at.desc', $limit);

    if ($logs === false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch activity logs',
            'logs' => []
        ]);
        exit();
    }

    echo json_encode([
        'status' => 'success',
        'logs' => $logs ?: []
    ]);
} catch (Exception $e) {
    error_log('Activity logs error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'logs' => []
    ]);
}

?>


