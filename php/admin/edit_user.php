
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

$user_id = $_POST['user_id'] ?? '';

if(empty($user_id)) {
    echo json_encode(array('status' => 'error', 'message' => 'User ID is required'));
    exit();
}

try {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(array('status' => 'error', 'message' => 'User not found'));
        exit();
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode($user);
    
} catch (Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Database error occurred'));
}
?>


