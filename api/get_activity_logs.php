<?php
// API endpoint for activity logs
require_once '../database/SupabaseConfig.php';
require_once '../database/DatabaseHelper.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

try {
    // Get limit from query parameter
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    
    // Get activity logs using helper function
    $logs = supabaseSelect('activity_logs', '*', [], 'created_at.desc', $limit);
    
    if ($logs !== false) {
        echo json_encode([
            'status' => 'success',
            'data' => $logs
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch activity logs'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Activity logs error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
}
?>
