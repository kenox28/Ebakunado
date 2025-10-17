<?php
/**
 * Get Notifications for Midwives
 * Retrieves system notifications and alerts
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';
require_once '../shared/access_control.php';

header('Content-Type: application/json');

// Check if user is midwife
if (!isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get recent notifications (last 50)
    $notifications = supabaseSelect('activity_logs', '*', 
        ['user_type' => 'midwife'], 
        'created_at.desc', 50);
    
    if (!$notifications) {
        $notifications = [];
    }
    
    // Format notifications for display
    $formatted_notifications = [];
    foreach ($notifications as $notification) {
        $formatted_notifications[] = [
            'id' => $notification['id'] ?? uniqid(),
            'title' => getNotificationTitle($notification['action'] ?? ''),
            'message' => $notification['description'] ?? '',
            'created_at' => $notification['created_at'] ?? date('Y-m-d H:i:s'),
            'is_read' => false, // Default to unread
            'type' => getNotificationType($notification['action'] ?? '')
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $formatted_notifications
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch notifications: ' . $e->getMessage()
    ]);
}

function getNotificationTitle($action) {
    $titles = [
        'login_success' => 'Login Successful',
        'logout' => 'Logout',
        'chr_request_approved' => 'CHR Request Approved',
        'chr_request_rejected' => 'CHR Request Rejected',
        'report_generated' => 'Report Generated',
        'profile_updated' => 'Profile Updated'
    ];
    
    return $titles[$action] ?? 'System Notification';
}

function getNotificationType($action) {
    if (strpos($action, 'approved') !== false) {
        return 'success';
    } elseif (strpos($action, 'rejected') !== false) {
        return 'error';
    } elseif (strpos($action, 'login') !== false) {
        return 'info';
    } else {
        return 'default';
    }
}
?>
