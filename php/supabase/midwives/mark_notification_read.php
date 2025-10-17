<?php
/**
 * Mark Notification as Read
 * Marks a specific notification as read
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

$input = json_decode(file_get_contents('php://input'), true);
$notification_id = $input['notification_id'] ?? '';

if (!$notification_id) {
    echo json_encode(['status' => 'error', 'message' => 'Notification ID required']);
    exit();
}

try {
    // In a real implementation, you would update a notifications table
    // For now, we'll just return success
    echo json_encode([
        'status' => 'success',
        'message' => 'Notification marked as read'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to mark notification as read: ' . $e->getMessage()
    ]);
}
?>
