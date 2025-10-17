<?php
/**
 * Get Immunization Records for Midwives
 * Retrieves immunization records with child information
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';
require_once '../shared/access_control.php';

header('Content-Type: application/json');

// Check if user is midwife and can access immunization records
if (!isset($_SESSION['midwife_id']) || !canAccessFeature('view_immunization', getCurrentUserType(), getCurrentUserData())) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Cannot access immunization records']);
    exit();
}

try {
    // Get immunization records
    $records = supabaseSelect('immunization_records', '*', [], 'date_given.desc', 1000);
    
    if (!$records) {
        $records = [];
    }
    
    // Enhance with child information
    $enhanced_records = [];
    foreach ($records as $record) {
        $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname', 
            ['baby_id' => $record['baby_id']], null, 1);
        
        if ($child_info && count($child_info) > 0) {
            $record['child_name'] = $child_info[0]['child_fname'] . ' ' . $child_info[0]['child_lname'];
        } else {
            $record['child_name'] = 'Unknown Child';
        }
        
        $enhanced_records[] = $record;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $enhanced_records,
        'count' => count($enhanced_records)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch immunization records: ' . $e->getMessage()
    ]);
}
?>
