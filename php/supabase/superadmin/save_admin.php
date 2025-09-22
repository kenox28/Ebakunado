<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['super_admin_id'])) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']); exit(); }

include '../../../database/SupabaseConfig.php';
include '../../../database/DatabaseHelper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_POST['admin_id'] ?? '';
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($admin_id) || empty($fname) || empty($lname) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
        exit();
    }

    if (strlen($password) < 8) { echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']); exit(); }
    if (!preg_match('/[A-Z]/', $password)) { echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one uppercase letter']); exit(); }
    if (!preg_match('/[a-z]/', $password)) { echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one lowercase letter']); exit(); }
    if (!preg_match('/[0-9]/', $password)) { echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one number']); exit(); }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) { echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one special character']); exit(); }

    try {
        $exists = supabaseSelect('admin', 'admin_id', ['admin_id' => $admin_id], null, 1);
        if ($exists && count($exists) > 0) {
            $ok = supabaseUpdate('admin', [
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email
            ], ['admin_id' => $admin_id]);
            echo json_encode(['status' => $ok !== false ? 'success' : 'error', 'message' => $ok !== false ? 'Admin updated successfully' : 'Failed to update admin']);
        } else {
            $dupe = supabaseSelect('admin', 'admin_id', ['email' => $email], null, 1);
            if ($dupe && count($dupe) > 0) { echo json_encode(['status' => 'error', 'message' => 'Email already exists']); exit(); }

            $hashed_password = md5($password);
            $ok = supabaseInsert('admin', [
                'admin_id' => $admin_id,
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'pass' => $hashed_password,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($ok !== false) {
                supabaseLogActivity($_SESSION['super_admin_id'], 'super_admin', 'CREATE', 'Created new admin: ' . $admin_id);
                echo json_encode(['status' => 'success', 'message' => 'Admin created successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create admin']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>


