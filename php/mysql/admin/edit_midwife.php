<?php
session_start();
include "../../../database/Database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['super_admin_id'])) {
    echo json_encode(array('status' => 'error', 'message' => 'Unauthorized access'));
    exit();
}

// Handle both GET (for fetching) and POST (for updating)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $midwife_id = $_GET['midwife_id'] ?? '';
} else {
    $midwife_id = $_POST['midwife_id'] ?? '';
}

if(empty($midwife_id)) {
    echo json_encode(array('status' => 'error', 'message' => 'Midwife ID is required'));
    exit();
}

try {
    $sql = "SELECT * FROM midwives WHERE midwife_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $midwife_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Midwife not found'));
        exit();
    }
    
    $midwife = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode(array('status' => 'success', 'data' => $midwife));
    
} catch (Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Database error occurred'));
}
?>
