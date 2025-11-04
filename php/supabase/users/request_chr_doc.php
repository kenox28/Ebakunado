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
$baby_id = $_POST['baby_id'] ?? '';
$request_type = strtolower(trim($_POST['request_type'] ?? ''));
if ($baby_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing baby_id']);
    exit();
}

// Validate request_type: transfer | school
if ($request_type === '' || !in_array($request_type, ['transfer','school'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request_type']);
    exit();
}

// Check if there's an existing approved request and if new records exist
$existingRequests = supabaseSelect(
    'chrdocrequest', 
    '*', 
    ['user_id' => $user_id, 'baby_id' => $baby_id, 'request_type' => $request_type], 
    'created_at.desc', 
    1
);

$allowNewRequest = true;
if ($existingRequests && count($existingRequests) > 0) {
    $existingRequest = $existingRequests[0];
    
    // If there's an approved request, check for newer vaccination records
    if ($existingRequest['status'] === 'approved' && $existingRequest['created_at']) {
        $latestVaccinations = supabaseSelect(
            'immunization_records',
            'date_given',
            ['baby_id' => $baby_id, 'status' => 'taken'],
            'date_given.desc',
            1
        );
        
        $hasNewerRecords = false;
        if ($latestVaccinations && count($latestVaccinations) > 0) {
            $latestVaccinationDate = $latestVaccinations[0]['date_given'];
            $chrDocumentDate = $existingRequest['created_at'];
            
            // Debug logging (can be removed in production)
            // error_log("CHR Request Debug - Baby ID: $baby_id");
            // error_log("CHR Request Debug - Latest vaccination date: $latestVaccinationDate");
            // error_log("CHR Request Debug - CHR document date: $chrDocumentDate");
            
            // Convert both dates to Y-m-d format for accurate comparison
            $vaccinationDateOnly = date('Y-m-d', strtotime($latestVaccinationDate));
            $chrDateOnly = date('Y-m-d', strtotime($chrDocumentDate));
            
            // Allow new request if vaccination date is same day or later than document date
            if ($latestVaccinationDate && $vaccinationDateOnly >= $chrDateOnly) {
                $hasNewerRecords = true;
            }
        }
        
        // Only allow new request if there are newer records
        if (!$hasNewerRecords) {
            echo json_encode(['status' => 'error', 'message' => 'Document is up-to-date. No new vaccination records found.']);
            exit();
        }
    } elseif ($existingRequest['status'] === 'pendingCHR') {
        echo json_encode(['status' => 'error', 'message' => 'You already have a pending request. Please wait for approval.']);
        exit();
    }
}

// Insert request into chrdocrequest table with status=pendingCHR
$insert = supabaseInsert('chrdocrequest', [
    'user_id' => $user_id,
    'baby_id' => $baby_id,
    'status' => 'pendingCHR',
    'request_type' => $request_type,
    'doc_url' => null,
    'created_at' => date('Y-m-d H:i:s')
]);

if ($insert === false) {
    $err = isset($supabase) && method_exists($supabase, 'getLastError') ? $supabase->getLastError() : null;
    echo json_encode(['status' => 'error', 'message' => 'Request failed', 'debug' => $err]);
    exit();
}

// Log activity: User requested CHR document
try {
    // Get user name for logging
    $user_info = supabaseSelect('users', 'fname,lname', ['user_id' => $user_id], null, 1);
    $user_name = 'User';
    if ($user_info && count($user_info) > 0) {
        $user_name = trim(($user_info[0]['fname'] ?? '') . ' ' . ($user_info[0]['lname'] ?? ''));
    }
    
    // Get child name for logging
    $child_info = supabaseSelect('child_health_records', 'child_fname,child_lname', ['baby_id' => $baby_id], null, 1);
    $child_name = 'Child';
    if ($child_info && count($child_info) > 0) {
        $child_name = trim(($child_info[0]['child_fname'] ?? '') . ' ' . ($child_info[0]['child_lname'] ?? ''));
    }
    
    $doc_type_label = ucfirst($request_type) . ' Copy';
    supabaseLogActivity(
        $user_id,
        'user',
        'CHR_DOC_REQUEST',
        $user_name . ' requested ' . $doc_type_label . ' CHR document for ' . $child_name . ' (Baby ID: ' . $baby_id . ')',
        $_SERVER['REMOTE_ADDR'] ?? null
    );
} catch (Exception $e) {
    // Log error but don't fail the request
    error_log('Failed to log CHR document request activity: ' . $e->getMessage());
}

echo json_encode(['status' => 'success', 'message' => 'Request submitted', 'data' => $insert]);
exit();
?>


