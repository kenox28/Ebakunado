<?php
// API endpoint for dashboard statistics
require_once '../database/SupabaseConfig.php';
require_once '../database/DatabaseHelper.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

try {
    // Get dashboard statistics using helper function
    $stats = supabaseGetDashboardStats();
    
    if ($stats !== false) {
        echo json_encode([
            'status' => 'success',
            'data' => $stats
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch dashboard statistics'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
}
?>
