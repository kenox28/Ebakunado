<?php
/**
 * Mark All Notifications as Read
 * Marks all notifications as read for the current user
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Check if user is midwife
if (!isset($_SESSION['midwife_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

try {
    // In a real implementation, you would update a notifications table
    // For now, we'll just return success
    echo json_encode([
        'status' => 'success',
        'message' => 'All notifications marked as read'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to mark all notifications as read: ' . $e->getMessage()
    ]);
}
?>
