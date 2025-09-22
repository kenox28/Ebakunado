<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['super_admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

try {
    $rows = supabaseSelect('admin', 'admin_id,fname,lname,email,created_at', [], 'created_at.desc');
    // Mirror MySQL behavior: return raw array when successful
    echo json_encode($rows ?: []);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
}
?>


