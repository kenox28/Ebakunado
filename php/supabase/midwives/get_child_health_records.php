<?php
/**
 * Get Child Health Records for Midwives
 * Retrieves child health records with filtering options
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';
require_once '../shared/access_control.php';

header('Content-Type: application/json');

// Check if user is midwife and can access child health records
if (!isset($_SESSION['midwife_id']) || !canAccessFeature('view_child_records', getCurrentUserType(), getCurrentUserData())) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Cannot access child health records']);
    exit();
}

try {
    // Get filter parameters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // Build conditions
    $conditions = [];
    
    if ($status) {
        $conditions['status'] = $status;
    }
    
    // Get child health records
    $records = supabaseSelect('child_health_records', '*', $conditions, 'date_created.desc', 1000);
    
    if (!$records) {
        $records = [];
    }
    
    // Filter by search term if provided
    if ($search) {
        $search_lower = strtolower($search);
        $records = array_filter($records, function($record) use ($search_lower) {
            $child_name = strtolower(($record['child_fname'] ?? '') . ' ' . ($record['child_lname'] ?? ''));
            $mother_name = strtolower(($record['mother_fname'] ?? '') . ' ' . ($record['mother_lname'] ?? ''));
            $baby_id = strtolower($record['baby_id'] ?? '');
            
            return strpos($child_name, $search_lower) !== false ||
                   strpos($mother_name, $search_lower) !== false ||
                   strpos($baby_id, $search_lower) !== false;
        });
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => array_values($records),
        'count' => count($records)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch child health records: ' . $e->getMessage()
    ]);
}
?>
