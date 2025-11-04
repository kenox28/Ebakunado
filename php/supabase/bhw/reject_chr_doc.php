<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../database/SupabaseConfig.php';
require_once __DIR__ . '/../../../database/DatabaseHelper.php';

try {
    if (!isset($_SESSION['bhw_id']) && !isset($_SESSION['midwife_id'])) {
        throw new Exception('Unauthorized');
    }
    
    $request_id = $_POST['request_id'] ?? '';
    if (empty($request_id)) {
        throw new Exception('Missing request_id');
    }
    
    // Get approver info
    $approver_id = $_SESSION['bhw_id'] ?? $_SESSION['midwife_id'] ?? null;
    $approver_type = isset($_SESSION['midwife_id']) ? 'midwife' : 'bhw';
    $approver_name = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
    
    // Fetch request details BEFORE deletion for activity log
    $reqRows = supabaseSelect('chrdocrequest', '*', ['id' => $request_id], null, 1);
    if (!$reqRows || count($reqRows) === 0) {
        throw new Exception('Request not found');
    }
    $req = $reqRows[0];
    
    // Get child and parent info for logging
    $child_name = 'Unknown Child';
    $parent_name = 'Unknown Parent';
    $request_type = strtoupper($req['request_type'] ?? 'UNKNOWN');
    
    // Get child name
    if (!empty($req['baby_id'])) {
        $childRows = supabaseSelect('child_health_records', 'child_fname,child_lname,user_id', ['baby_id' => $req['baby_id']], null, 1);
        if ($childRows && count($childRows) > 0) {
            $child_name = trim(($childRows[0]['child_fname'] ?? '') . ' ' . ($childRows[0]['child_lname'] ?? ''));
            
            // Get parent name
            $user_id = $childRows[0]['user_id'] ?? '';
            if (!empty($user_id) && strpos($user_id, 'FAM-') !== 0) {
                // Not a family code, so it's a user_id
                $userRows = supabaseSelect('users', 'fname,lname', ['user_id' => $user_id], null, 1);
                if ($userRows && count($userRows) > 0) {
                    $parent_name = trim(($userRows[0]['fname'] ?? '') . ' ' . ($userRows[0]['lname'] ?? ''));
                }
            }
        }
    }
    
    // Delete the request from database
    $ok = supabaseDelete('chrdocrequest', ['id' => $request_id]);
    
    if ($ok !== false) {
        // Log activity: BHW/Midwife rejected and removed CHR document request
        try {
            supabaseLogActivity(
                $approver_id,
                $approver_type,
                'CHR_DOC_REQUEST_REJECTED',
                $approver_name . ' rejected and removed CHR document request for ' . $child_name . ', child of ' . $parent_name . ' (Request ID: ' . $request_id . ', Type: ' . $request_type . ')',
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (Exception $e) {
            // Log error but don't fail the rejection
            error_log('Failed to log CHR doc request rejection activity: ' . $e->getMessage());
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'CHR document request rejected and removed successfully'
        ]);
    } else {
        throw new Exception('Failed to delete request');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>

