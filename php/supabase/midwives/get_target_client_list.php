<?php
/**
 * Get Target Client List for Midwives
 * Retrieves vaccination matrix data for all children
 */

session_start();
require_once '../../../database/SupabaseConfig.php';
require_once '../../../database/DatabaseHelper.php';
require_once '../shared/access_control.php';

header('Content-Type: application/json');

// Check if user is midwife and can access target client list
if (!isset($_SESSION['midwife_id']) || !canAccessFeature('view_target_client_list', getCurrentUserType(), getCurrentUserData())) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Cannot access target client list']);
    exit();
}

try {
    // Get all accepted child health records
    $children = supabaseSelect('child_health_records', '*', ['status' => 'accepted'], 'child_fname.asc', 1000);
    
    if (!$children) {
        $children = [];
    }
    
    // Enhance with vaccination data
    $enhanced_children = [];
    foreach ($children as $child) {
        $child['child_name'] = $child['child_fname'] . ' ' . $child['child_lname'];
        
        // Get vaccination records for this child
        $vaccinations = supabaseSelect('immunization_records', '*', ['baby_id' => $child['baby_id']], 'date_given.asc', 100);
        
        // Initialize vaccine statuses
        $child['bcg_status'] = 'Pending';
        $child['hepab1_status'] = 'Pending';
        $child['pentavalent_status'] = 'Pending';
        $child['opv_status'] = 'Pending';
        $child['pcv_status'] = 'Pending';
        $child['mcv_status'] = 'Pending';
        
        if ($vaccinations) {
            foreach ($vaccinations as $vaccination) {
                $vaccine_name = $vaccination['vaccine_name'] ?? '';
                $dose_number = $vaccination['dose_number'] ?? 1;
                $status = $vaccination['status'] ?? 'pending';
                
                if ($status === 'completed' || $status === 'taken') {
                    switch ($vaccine_name) {
                        case 'BCG':
                            $child['bcg_status'] = 'Completed';
                            break;
                        case 'HEPAB1':
                            $child['hepab1_status'] = 'Completed';
                            break;
                        case 'Pentavalent':
                            $child['pentavalent_status'] = 'Completed';
                            break;
                        case 'OPV':
                            $child['opv_status'] = 'Completed';
                            break;
                        case 'PCV':
                            $child['pcv_status'] = 'Completed';
                            break;
                        case 'MCV':
                            $child['mcv_status'] = 'Completed';
                            break;
                    }
                }
            }
        }
        
        $enhanced_children[] = $child;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $enhanced_children,
        'count' => count($enhanced_children)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch target client list: ' . $e->getMessage()
    ]);
}
?>
