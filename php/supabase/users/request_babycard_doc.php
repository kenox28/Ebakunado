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
if ($baby_id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing baby_id']);
    exit();
}

// Note: Baby Card requests use request_type='school' or 'transfer' like CHR requests
// You can modify this to accept request_type from POST, or default to 'school'
$request_type = strtolower(trim($_POST['request_type'] ?? 'school'));
if ($request_type !== 'school' && $request_type !== 'transfer') {
    $request_type = 'school'; // Default to 'school'
}

// Check if there's an existing pending request for this baby_id
$existingRequests = supabaseSelect(
    'chrdocrequest', 
    '*', 
    ['user_id' => $user_id, 'baby_id' => $baby_id, 'status' => 'pendingCHR'], 
    'created_at.desc', 
    1
);

if ($existingRequests && count($existingRequests) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You already have a pending request. Please wait for approval.']);
    exit();
}

// Insert request into chrdocrequest table with status=pendingCHR and request_type='school' or 'transfer'
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

// Log activity: User requested Baby Card document
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
    
    supabaseLogActivity(
        $user_id,
        'user',
        'BABYCARD_DOC_REQUEST',
        $user_name . ' requested Baby Card document for ' . $child_name . ' (Baby ID: ' . $baby_id . ')',
        $_SERVER['REMOTE_ADDR'] ?? null
    );
} catch (Exception $e) {
    // Log error but don't fail the request
    error_log('Failed to log Baby Card document request activity: ' . $e->getMessage());
}

echo json_encode(['status' => 'success', 'message' => 'Baby Card request submitted', 'data' => $insert]);
exit();
?>

