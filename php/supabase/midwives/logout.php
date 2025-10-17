<?php
/**
 * Midwife Logout Handler
 * Handles midwife logout and session cleanup
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

// Check if midwife is logged in
if (!isset($_SESSION['midwife_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No active session found'
    ]);
    exit();
}

try {
    $midwife_id = $_SESSION['midwife_id'];
    $midwife_name = ($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '');
    
    // Log the logout activity
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    supabaseLogActivity($midwife_id, 'midwife', 'logout', 'Midwife logged out successfully: ' . $midwife_name, $ip);
    
    // Clear session data
    session_unset();
    session_destroy();
    
    // Clean output buffer
    ob_clean();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Midwife logged out successfully',
        'debug' => [
            'had_session' => ($midwife_id !== null)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Logout failed: ' . $e->getMessage()
    ]);
}
?>
