<?php
session_start();
header('Content-Type: application/json');

// Check if super admin is logged in
if (!isset($_SESSION['super_admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../../database/Database.php';

try {
    $stats = [];
    
    // Count users
    $user_sql = "SELECT COUNT(*) as count FROM users";
    $user_result = $connect->query($user_sql);
    if ($user_result) {
        $stats['users'] = $user_result->fetch_assoc()['count'];
    } else {
        $stats['users'] = 0;
    }
    
    // Count admins
    $admin_sql = "SELECT COUNT(*) as count FROM admin";
    $admin_result = $connect->query($admin_sql);
    if ($admin_result) {
        $stats['admins'] = $admin_result->fetch_assoc()['count'];
    } else {
        $stats['admins'] = 0;
    }
    
    // Count BHW
    $bhw_sql = "SELECT COUNT(*) as count FROM bhw";
    $bhw_result = $connect->query($bhw_sql);
    if ($bhw_result) {
        $stats['bhw'] = $bhw_result->fetch_assoc()['count'];
    } else {
        $stats['bhw'] = 0;
    }
    
    // Count Midwives
    $midwives_sql = "SELECT COUNT(*) as count FROM midwives";
    $midwives_result = $connect->query($midwives_sql);
    if ($midwives_result) {
        $stats['midwives'] = $midwives_result->fetch_assoc()['count'];
    } else {
        $stats['midwives'] = 0;
    }
    
    // Count Locations
    $locations_sql = "SELECT COUNT(*) as count FROM locations";
    $locations_result = $connect->query($locations_sql);
    if ($locations_result) {
        $stats['locations'] = $locations_result->fetch_assoc()['count'];
    } else {
        $stats['locations'] = 0;
    }
    
    // Count Activity Logs
    $logs_sql = "SELECT COUNT(*) as count FROM activity_logs";
    $logs_result = $connect->query($logs_sql);
    if ($logs_result) {
        $stats['logs'] = $logs_result->fetch_assoc()['count'];
    } else {
        $stats['logs'] = 0;
    }
    
    echo json_encode([
        'status' => 'success',
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
?>
