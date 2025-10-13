<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get all children for this user (both accepted and pending)
    $children = supabaseSelect(
        'child_health_records', 
        'baby_id,child_fname,child_lname,status', 
        ['user_id' => $user_id]
    );

    if (!$children) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit();
    }

    $baby_ids = array_column($children, 'baby_id');
    $children_with_status = [];

    foreach ($children as $child) {
        $baby_id = $child['baby_id'];
        
        // Get latest CHR document requests for this child
        $chrRequests = supabaseSelect(
            'chrdocrequest',
            '*',
            ['user_id' => $user_id, 'baby_id' => $baby_id],
            'created_at.desc'
        );

        // Check for newer vaccination records
        $hasNewerRecords = false;
        $latestRequest = null;
        
        if ($chrRequests && count($chrRequests) > 0) {
            $latestRequest = $chrRequests[0];
            
            // Check if there are newer vaccination records since the last CHR document
            if ($latestRequest['status'] === 'approved' && $latestRequest['created_at']) {
                $latestVaccinations = supabaseSelect(
                    'immunization_records',
                    'date_given',
                    ['baby_id' => $baby_id, 'status' => 'taken'],
                    'date_given.desc',
                    1
                );
                
                if ($latestVaccinations && count($latestVaccinations) > 0) {
                    $latestVaccinationDate = $latestVaccinations[0]['date_given'];
                    $chrDocumentDate = $latestRequest['created_at'];
                    
                    // Convert both dates to Y-m-d format for accurate comparison
                    $vaccinationDateOnly = date('Y-m-d', strtotime($latestVaccinationDate));
                    $chrDateOnly = date('Y-m-d', strtotime($chrDocumentDate));
                    
                    // Check if vaccination date is same day or later than document date
                    if ($latestVaccinationDate && $vaccinationDateOnly >= $chrDateOnly) {
                        $hasNewerRecords = true;
                    }
                }
            }
        }

        // Determine CHR status
        $chrStatus = 'none';
        if ($latestRequest) {
            if ($latestRequest['status'] === 'pendingCHR') {
                $chrStatus = 'pending';
            } elseif ($latestRequest['status'] === 'approved') {
                if ($hasNewerRecords) {
                    $chrStatus = 'new_records';
                } else {
                    $chrStatus = 'approved';
                }
            }
        }

        $children_with_status[] = [
            'baby_id' => $baby_id,
            'child_fname' => $child['child_fname'],
            'child_lname' => $child['child_lname'],
            'chr_status' => $chrStatus,
            'latest_request' => $latestRequest,
            'has_newer_records' => $hasNewerRecords
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $children_with_status
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
