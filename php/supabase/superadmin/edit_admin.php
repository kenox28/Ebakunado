<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['super_admin_id'])) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']); exit(); }

include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['admin_id'])) {
    $admin_id = trim($_GET['admin_id']);
    try {
        $rows = supabaseSelect('admin', 'admin_id,fname,lname,email,created_at', ['admin_id' => $admin_id], null, 1);
        if ($rows && count($rows) > 0) {
            echo json_encode(['status' => 'success', 'data' => $rows[0]]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Admin not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = trim($_POST['admin_id'] ?? '');
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';

    if (empty($admin_id) || empty($fname) || empty($lname) || empty($email)) { echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']); exit(); }

    try {
        $dupe = supabaseSelect('admin', 'admin_id', ['email' => $email], null, 1);
        if ($dupe && isset($dupe[0]['admin_id']) && trim($dupe[0]['admin_id']) !== $admin_id) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists for another admin']);
            exit();
        }

        $ok = supabaseUpdate('admin', [
            'fname' => $fname,
            'lname' => $lname,
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['admin_id' => $admin_id]);

        if ($ok !== false) {
            supabaseLogActivity($_SESSION['super_admin_id'], 'super_admin', 'UPDATE', 'Updated admin: ' . $admin_id);
            echo json_encode(['status' => 'success', 'message' => 'Admin updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update admin']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>


