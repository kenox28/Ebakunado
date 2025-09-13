<?php
session_start();
include "../../database/Database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
    exit();
}

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request method'));
    exit();
}

try {
    $sql = "SELECT * FROM midwives ORDER BY created_at DESC";
    $result = mysqli_query($connect, $sql);
    
    if (!$result) {
        throw new Exception("Failed to fetch Midwives data: " . mysqli_error($connect));
    }
    
    $midwives_data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $midwives_data[] = $row;
    }
    
    echo json_encode($midwives_data);
    
} catch (Exception $e) {
    error_log("Show Midwives error: " . $e->getMessage());
    echo json_encode(array('status' => 'error', 'message' => 'Database error occurred'));
}
?>
