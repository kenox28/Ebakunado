<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['super_admin_id'])) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']); exit(); }

include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

try {
    $stats = [];
    $stats['users'] = supabaseCount('users');
    $stats['admins'] = supabaseCount('admin');
    $stats['bhw'] = supabaseCount('bhw');
    $stats['midwives'] = supabaseCount('midwives');
    $stats['locations'] = supabaseCount('locations');
    $stats['logs'] = supabaseCount('activity_logs');

    echo json_encode(['status' => 'success', 'stats' => $stats]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
}
?>


