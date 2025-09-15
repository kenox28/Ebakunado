<?php
session_start();

// Check if admin or super admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once '../../database/Database.php';

header('Content-Type: application/json');

try {
    // Get limit parameter (default to all if not specified)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    
    // Build query
    $sql = "SELECT log_id, user_id, user_type, action_type, description, ip_address, created_at 
            FROM activity_logs 
            ORDER BY created_at DESC";
    
    if ($limit && $limit > 0) {
        $sql .= " LIMIT " . $limit;
    }
    
    $result = $connect->query($sql);
    
    if ($result) {
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'logs' => $logs
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch activity logs',
            'logs' => []
        ]);
    }
    
} catch (Exception $e) {
    error_log("Activity logs error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred',
        'logs' => []
    ]);
}

$connect->close();
?>
