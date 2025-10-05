<?php
session_start();
include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get pending child health records for this user
    $pending_records = supabaseSelect(
        'child_health_records', 
        'id,baby_id,child_fname,child_lname,status,date_created', 
        ['user_id' => $user_id, 'status' => 'pending'], 
        'date_created.desc'
    );

    if (!$pending_records) {
        $pending_records = [];
    }

    echo json_encode([
        'status' => 'success', 
        'data' => $pending_records
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
