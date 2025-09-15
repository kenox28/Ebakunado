<?php
session_start();

// Check if admin or super admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include "../../database/Database.php";

header('Content-Type: application/json');

try {
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $result = $connect->query($sql);
    
    if ($result) {
        $data = array();
        for($i = 0; $i < $result->num_rows; $i++) {
            $data[] = $result->fetch_assoc();
        }
        echo json_encode($data);
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    error_log("Show users error: " . $e->getMessage());
    echo json_encode([]);
}
?>