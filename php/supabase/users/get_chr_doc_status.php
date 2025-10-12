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
$baby_id = $_GET['baby_id'] ?? '';
$request_type = strtolower(trim($_GET['request_type'] ?? ''));
if ($baby_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing baby_id']);
    exit();
}

$where = ['user_id' => $user_id, 'baby_id' => $baby_id];
if ($request_type !== '') { $where['request_type'] = $request_type; }
$rows = supabaseSelect('chrdocrequest', '*', $where, 'created_at.desc', 1);
$row = ($rows && count($rows) > 0) ? $rows[0] : null;

// Check if there are newer vaccination records since the last CHR document
$hasNewerRecords = false;
if ($row && $row['status'] === 'approved' && $row['created_at']) {
    // Get the latest vaccination record date for this baby
    $latestVaccinations = supabaseSelect(
        'immunization_records',
        'date_given',
        ['baby_id' => $baby_id, 'status' => 'taken'],
        'date_given.desc',
        1
    );
    
    if ($latestVaccinations && count($latestVaccinations) > 0) {
        $latestVaccinationDate = $latestVaccinations[0]['date_given'];
        $chrDocumentDate = $row['created_at'];
        
        // Debug logging (can be removed in production)
        // error_log("CHR Debug - Baby ID: $baby_id");
        // error_log("CHR Debug - Latest vaccination date: $latestVaccinationDate");
        // error_log("CHR Debug - CHR document date: $chrDocumentDate");
        
        // Compare dates - allow new requests if vaccination is on same day or later
        // Convert both dates to Y-m-d format for accurate comparison
        $vaccinationDateOnly = date('Y-m-d', strtotime($latestVaccinationDate));
        $chrDateOnly = date('Y-m-d', strtotime($chrDocumentDate));
        
        // Allow new request if vaccination date is same day or later than document date
        if ($latestVaccinationDate && $vaccinationDateOnly >= $chrDateOnly) {
            $hasNewerRecords = true;
        }
    }
}

// Add the hasNewerRecords flag to the response
$responseData = $row ? array_merge($row, ['has_newer_records' => $hasNewerRecords]) : ['has_newer_records' => false];

echo json_encode(['status' => 'success', 'data' => $responseData, 'request_type' => $request_type]);
exit();
?>


