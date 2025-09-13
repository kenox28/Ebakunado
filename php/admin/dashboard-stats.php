<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once '../../database/Database.php';

header('Content-Type: application/json');

try {
    // Get total users count
    $users_sql = "SELECT COUNT(*) as count FROM users";
    $users_result = $connect->query($users_sql);
    $users_count = $users_result ? $users_result->fetch_assoc()['count'] : 0;
    
    // Get total BHWs count
    $bhws_sql = "SELECT COUNT(*) as count FROM bhw";
    $bhws_result = $connect->query($bhws_sql);
    $bhws_count = $bhws_result ? $bhws_result->fetch_assoc()['count'] : 0;
    
    // Get total Midwives count
    $midwives_sql = "SELECT COUNT(*) as count FROM midwives";
    $midwives_result = $connect->query($midwives_sql);
    $midwives_count = $midwives_result ? $midwives_result->fetch_assoc()['count'] : 0;
    
    // Get total locations count
    $locations_sql = "SELECT COUNT(*) as count FROM locations";
    $locations_result = $connect->query($locations_sql);
    $locations_count = $locations_result ? $locations_result->fetch_assoc()['count'] : 0;
    
    // Get total activity logs count
    $logs_sql = "SELECT COUNT(*) as count FROM activity_logs";
    $logs_result = $connect->query($logs_sql);
    $logs_count = $logs_result ? $logs_result->fetch_assoc()['count'] : 0;
    
    $stats = [
        'users' => (int)$users_count,
        'bhws' => (int)$bhws_count,
        'midwives' => (int)$midwives_count,
        'locations' => (int)$locations_count,
        'activity_logs' => (int)$logs_count
    ];
    
    echo json_encode([
        'status' => 'success',
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch dashboard statistics',
        'stats' => [
            'users' => 0,
            'bhws' => 0,
            'midwives' => 0,
            'locations' => 0,
            'activity_logs' => 0
        ]
    ]);
}

$connect->close();
?>
