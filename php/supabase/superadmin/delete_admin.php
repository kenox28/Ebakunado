<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['super_admin_id'])) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']); exit(); }

include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = trim($_POST['admin_id'] ?? '');
    if (empty($admin_id)) { echo json_encode(['status' => 'error', 'message' => 'Admin ID is required']); exit(); }

    try {
        $exists = supabaseSelect('admin', 'admin_id', ['admin_id' => $admin_id], null, 1);
        if (!$exists || count($exists) === 0) { echo json_encode(['status' => 'error', 'message' => 'Admin not found']); exit(); }

        $ok = supabaseDelete('admin', ['admin_id' => $admin_id]);
        if ($ok !== false) {
            supabaseLogActivity($_SESSION['super_admin_id'], 'super_admin', 'DELETE', 'Deleted admin: ' . $admin_id);
            echo json_encode(['status' => 'success', 'message' => 'Admin deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete admin']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>


