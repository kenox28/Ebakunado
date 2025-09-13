<?php
session_start();
header('Content-Type: application/json');

// Check if super admin is logged in
if (!isset($_SESSION['super_admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

include '../../database/Database.php';

// Check database connection
if (!$connect) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

try {
    $sql = "SELECT admin_id, fname, lname, email, created_at FROM admin ORDER BY created_at DESC";
    $result = $connect->query($sql);
    
    if ($result) {
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        echo json_encode($admins);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $connect->error]);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
}

$connect->close();
?>
