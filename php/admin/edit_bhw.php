<?php
session_start();
include "../../database/Database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request method'));
    exit();
}

$bhw_id = $_POST['bhw_id'] ?? '';

if(empty($bhw_id)) {
    echo json_encode(array('status' => 'error', 'message' => 'BHW ID is required'));
    exit();
}

try {
    $sql = "SELECT * FROM bhw WHERE bhw_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $bhw_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(array('status' => 'error', 'message' => 'BHW not found'));
        exit();
    }
    
    $bhw = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode($bhw);
    
} catch (Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Database error occurred'));
}
?>
